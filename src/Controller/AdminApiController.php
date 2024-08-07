<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product\Product;
use App\Entity\Product\ProductImage;
use App\Entity\Product\ProductTaxon;
use App\Entity\Product\ProductVariant;
use App\Entity\Taxonomy\Taxon;
use App\Entity\Taxonomy\TaxonImage;
use App\Form\ProductType;
use App\Form\ProductVariantType;
use App\Form\TaxonType;
use App\Repository\ProductRepository;
use App\Repository\TaxonRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Intervention\Image\ImageManagerStatic;
use Swift_Mailer;
use Sylius\Bundle\ProductBundle\Doctrine\ORM\ProductVariantRepository;
use Sylius\Bundle\ProductBundle\Form\Type\ProductGenerateVariantsType;
use Sylius\Component\Core\Uploader\ImageUploaderInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class AdminApiController extends AbstractController
{

    /**
     * @param FormInterface $form
     * @param string $name
     * @return array
     */
    private function getErrors(FormInterface $form, $name="")
    {
        $errors = array();

        /** @var FormError $error */
        foreach ($form->getErrors() as $error) {

            $parameters = $error->getMessageParameters();
            $message = $error->getMessage();

            if( isset($parameters['{{ extra_fields }}']) )
                $message .= ' : '.$parameters['{{ extra_fields }}'];

            if( $name != $form->getName() )
                $errors[$form->getName()][] = $message;
            else
                $errors[] = $message;
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrors($childForm, $childForm->getName())) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }


    /**
     * Get product list
     *
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
     */
    public function getProducts(Request $request, ProductRepository $productRepository){

        list($limit, $offset) = $this->getPagination($request);
        $products = $productRepository->findBy([], null, $limit, $offset);

        foreach ($products as $product)
            $data[] = $productRepository->hydrate($product);

        return $this->json([
            'items'=>$data,
            'count'=>$productRepository->count([])
        ]);
    }


    /**
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param Swift_Mailer $mailer
     * @return JsonResponse
     */
    public function cleanup(Request $request, ProductRepository $productRepository, EntityManagerInterface $entityManager, Swift_Mailer $mailer){

        if( !$syncedAt = $request->get('syncedAt') )
            return $this->json(['message'=>'syncedAt parameter is missing'], 500);

        if( !$products_code = $request->get('enabledProducts') )
            return $this->json(['message'=>'enabledProducts parameter is missing'], 500);

        $products = $productRepository->findDivergentes($syncedAt);

        $failed = $products_code;
        $disabled = [];
        $conflicted = [];

        foreach ($products as $product){

            if( !in_array($product->getCode(), $products_code) ){

                $product->setEnabled(false);

                $this->persist($product, $entityManager);

                $disabled[] = $product->getCode();
            }
            else{

                $conflicted[] = $product->getCode();
            }

            if( isset($failed[$product->getCode()]) )
                unset($failed[$product->getCode()]);
        }

        if( (count($conflicted) || count($failed)) && $email = $request->get('email') ){

            try{

                $message = (new \Swift_Message())
                    ->setSubject('[Syncro] Il y a '.count($conflicted).' produit(s) en conflict, '.count($failed).' en échec')
                    ->setFrom(['no-reply@volkswagen.fr' => 'Volkswagen'])
                    ->setTo($email);

                $message->setBody($this->renderView('@SyliusAdmin/emails/alert.html.twig', [
                    'message'=>'En conflict : <br/>'.implode(', ', $conflicted).'<br/><br/>En echec : <br/>'.implode(', ', $failed)
                ]), 'text/html');

                $mailer->send($message);
            }
            catch (\Throwable $t){

                return $this->json(['message'=>$t->getMessage()], 500);
            }
        }

        return $this->json([
            'disabled'=>$disabled,
            'conflicted'=>$conflicted,
            'failed'=>$failed
        ]);
    }

    /**
     * Bulk update stock
     *
     * @param Request $request
     * @param ProductVariantRepository $productVariantRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function updateStock(Request $request, ProductVariantRepository $productVariantRepository, EntityManagerInterface $entityManager){

        set_time_limit(360);

        $data = $request->request->all();
        $syncedAt = new DateTime();

        $updated = [];
        $failed = [];

        foreach ($data as $code=>$stock){

            $stock = min(99999, max(0, intval($stock)));

            /** @var ProductVariant $productVariant */
            if( $productVariant = $productVariantRepository->findOneBy(['code'=>$code]) ){

                $productVariant->setOnHand(intval($stock));
                $productVariant->setTracked(false);
                $productVariant->setSyncedAt($syncedAt);

                $this->persist($productVariant, $entityManager);

                $updated[] = $code;
            }
            else{

                $failed[] = $code;
            }
        }


        return $this->json([
            'updated'=>$updated,
            'failed'=>$failed
        ]);
    }


    /**
     * Create products or update if exists
     *
     * @param Request $request
     * @param ImageUploaderInterface $imageUploader
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param ManagerRegistry $managerRegistry
     * @return JsonResponse
     */
    public function createProducts(Request $request, ImageUploaderInterface $imageUploader, ProductRepository $productRepository, EntityManagerInterface $entityManager, ManagerRegistry $managerRegistry){

        set_time_limit(360);

	    $query = $request->query->all();
	    $products = $request->request->all();

        $output = [
            'created'=>[],
            'updated'=>[],
            'failed'=>[]
        ];

        $syncedAt = new DateTime();

        foreach ($products as $data) {

            if( !is_string($data['code']??'') || empty($data['code']??'') )
                continue;

            try{

                $created = false;

                if (!$product = $productRepository->findOneByCode($data['code'])) {
                    $product = new Product();
                    $created = true;
                }

                $product->setEnabled(true);

                $productTaxons = clone $product->getProductTaxons();

                $form = $this->submitForm(ProductType::class, $data, $product);

                if ( !$form->isValid() ){

                    $output['failed'][$product->getCode()] = $this->getErrors($form);
                    continue;
                }

                if (!empty($data['variants'] ?? false)) {

                    //ProductGenerateVariantsType require a product with option, maybe there is a better way to do this
                    $form = $this->submitForm(ProductGenerateVariantsType::class, $data, $product, true, ['csrf_protection' => false]);

                } else {

                    foreach ($product->getOptions() as $option )
                        $product->removeOption($option);

                    if (!$productVariant = $product->getVariant()) {

                        $productVariant = new ProductVariant();
                        $productVariant->setOnHand(0);

                        $product->addVariant($productVariant);
                    }

                    $productVariant->setEnabled(true);

                    $data['version'] = $productVariant->getVersion();
                    $form = $this->submitForm(ProductVariantType::class, $data, $productVariant, false);
                }

                if (!$form->isValid()){

                    $output['failed'][$product->getCode()] = $this->getErrors($form);
                    continue;
                }

                $this->processMedias($syncedAt, $product, $imageUploader, $query['force_media_update']??false);
                $this->processProduct($product, $productTaxons);

                $this->persist($product, $entityManager);

                $output[$created?'created':'updated'][] = $product->getCode();
            }
            catch (\Throwable $t){

                if( !$entityManager->isOpen() ){

                    $managerRegistry->resetManager();
                    $entityManager = $managerRegistry->getManager();
                }

                $output['failed'][$product->getCode()] = $t->getMessage();
            }
        }

        return $this->json($output);
    }


    /**
     * Create product or update if exists
     *
     * @param Request $request
     * @param ImageUploaderInterface $imageUploader
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function createProduct(Request $request, ImageUploaderInterface $imageUploader, ProductRepository $productRepository, EntityManagerInterface $entityManager){

		$data = $request->request->all();
        $query = $request->query->all();

        $syncedAt = new DateTime();
        $created = false;

        if( !is_string($data['code']??'') || empty($data['code']??'') )
            return $this->json(['code is empty'], 500);

        if (!$product = $productRepository->findOneByCode($data['code'])) {

            $product = new Product();
            $created = true;
        }

        $product->setEnabled(true);

        $productTaxons = clone $product->getProductTaxons();

        $form = $this->submitForm(ProductType::class, $data, $product);

        if (!$form->isValid())
            return $this->json($this->getErrors($form), 500);

        if (!empty($data['variants'] ?? false)) {

            //ProductGenerateVariantsType require a product with option, maybe there is a better way to do this
            $form = $this->submitForm(ProductGenerateVariantsType::class, $data, $product, true, ['csrf_protection' => false]);

        } else {

            foreach ($product->getOptions() as $option )
                $product->removeOption($option);

            if (!$productVariant = $product->getVariant()) {

                $productVariant = new ProductVariant();
                $productVariant->setOnHand(0);

                $product->addVariant($productVariant);
            }

            $productVariant->setEnabled(true);

            $data['version'] = $productVariant->getVersion();
            $form = $this->submitForm(ProductVariantType::class, $data, $productVariant, false);
        }

        if (!$form->isValid())
            return $this->json($this->getErrors($form), 500);

		$this->processMedias($syncedAt, $product, $imageUploader, $query['force_media_update']??false);
        $this->processProduct($product, $productTaxons);

        $this->persist($product, $entityManager);

        $product = $productRepository->hydrate($product);
        $product['status'] = $created ? 'created' : 'updated';

        return $this->json($product);
    }


    /**
     * Update product
     *
     * @param $code
     * @param Request $request
     * @param ImageUploaderInterface $imageUploader
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function updateProduct($code, Request $request, ImageUploaderInterface $imageUploader, ProductRepository $productRepository, EntityManagerInterface $entityManager){

        $query = $request->query->all();
        $syncedAt = new DateTime();

        if( !$product = $productRepository->findOneByCode($code) )
            $this->json('Product does not exists', 500);

        $productTaxons = clone $product->getProductTaxons();

        $form = $this->submitForm(ProductType::class, $request, $product);

        if( !$form->isValid() )
            return $this->json($this->getErrors($form), 500);

        if( $request->get('variants') && !empty($request->get('variants'))  ){

            $form = $this->submitForm(ProductGenerateVariantsType::class, $request, $product, true, ['csrf_protection' => false]);
        }
        else{

            if( !$productVariant = $product->getVariant() ){

                $productVariant = new ProductVariant();
                $product->addVariant($productVariant);
            }

            $data = $request->request->all();
            unset($data['translations']);
            $data['onHand'] = 0;

            $form = $this->submitForm(ProductVariantType::class, $data, $productVariant, true, ['csrf_protection' => false]);
        }

        if( !$form->isValid() )
            return $this->json($this->getErrors($form), 500);

	    $this->processMedias($syncedAt, $product, $imageUploader, $query['force_media_update']??false);
        $this->processProduct($product, $productTaxons);

        $this->persist($product, $entityManager);

        return $this->json($productRepository->hydrate($product));
    }


    /**
     * Get taxon list
     *
     * @param Request $request
     * @param TaxonRepository $taxonRepository
     * @return JsonResponse
     */
    public function getTaxons(Request $request, TaxonRepository $taxonRepository){

        list($limit, $offset) = $this->getPagination($request);
        $taxons = $taxonRepository->findBy([], null, $limit, $offset);

        foreach ($taxons as $taxon)
            $data[] = $taxonRepository->hydrate($taxon);

        return $this->json([
            'items'=>$data,
            'count'=>$taxonRepository->count([])
        ]);
    }


    /**
     * @param Product $product
     * @param ProductTaxon[] $originalProductTaxons
     * @return void
     */
    private function processProduct(Product $product, $originalProductTaxons){

        /** @var ProductTaxon[] $taxons */
        $productTaxons = $product->getProductTaxons();
        $position = 0;
        $taxons = [];

        $_originalProductTaxons = [];

        foreach ($originalProductTaxons as $originalProductTaxon)
            $_originalProductTaxons[$originalProductTaxon->getTaxon()->getCode()] = $originalProductTaxon;

        foreach ($productTaxons as $productTaxon ){

            $position = max($position, $productTaxon->getPosition());
            $taxons[] = $productTaxon->getTaxon()->getCode();
        }

        foreach ($productTaxons as $productTaxon){

            $taxonAncestors = $productTaxon->getTaxon()->getAncestors();

            foreach ($taxonAncestors as $taxonAncestor){

                $code = $taxonAncestor->getCode();

                if( $productTaxon = $_originalProductTaxons[$code]??false ){

                    $product->addProductTaxon($productTaxon);
                    $taxons[] = $code;
                }
                elseif( !in_array($code, $taxons) ){

                    $position++;

                    $productTaxon = new ProductTaxon();
                    $productTaxon->setTaxon($taxonAncestor);
                    $productTaxon->setPosition($position);

                    $product->addProductTaxon($productTaxon);

                    $taxons[] = $code;
                }
            }
        }
    }


    /**
     * @param $entity
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    private function persist($entity, EntityManagerInterface $entityManager){

        $entityManager->persist($entity);
        $entityManager->flush();
        $entityManager->refresh($entity);
    }


    /**
     * @param DateTime $syncedAt
     * @param Product|Taxon $entity
     * @param ImageUploaderInterface $imageUploader
     * @param bool $force_update
     * @return void
     */
	private function processMedias(DateTime $syncedAt, $entity, ImageUploaderInterface $imageUploader, $force_update=false){

		$medias = $entity->getMedias();
        $entityImages = $entity->getImages();
        $this->orderMedias($medias);
        if( !empty($medias) ) {

            foreach ($medias as $i => $media) {

                $entityImage = $entityImages[$i];

                if (!$entityImage)
                    $entityImage = $entity instanceof Product ? new ProductImage() : new TaxonImage();

                $medias = $entity instanceof Product ? $this->orderMedias($medias) : $medias;

                if ($force_update || $entityImage->getUpdatedAt() < $media['updatedAt']) {

                    if ($path = $entityImage->getPath()) {

                        $imageUploader->remove($path);
                        $entity->removeImage($entityImage);
                    }

                    $tmpfname = @tempnam("/tmp", "UL_IMAGE");

                    $img = ImageManagerStatic::make($media['url']);

                    $img->resize(1920, 1920, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });

                    $img->save($tmpfname, 95);

                    $entityImage->setFile(new UploadedFile($tmpfname, basename($media['url'])));
                    $entityImage->setUpdatedAt($media['updatedAt']);

                    $imageUploader->upload($entityImage);

                    $entity->addImage($entityImage);

                    unlink($tmpfname);
                }

                $entityImage->setType($media['type']);
                $entityImage->setSyncedAt($syncedAt);
            }

            /** @var ProductImage[] $images */
            $entityImages = $entity->getImages();

            foreach ($entityImages as $entityImage) {

                if ($entityImage->getSyncedAt() < $syncedAt) {

                    $imageUploader->remove($entityImage->getPath());
                    $entity->removeImage($entityImage);
                }
            }

            $entity->setSyncedAt($syncedAt);
        }
    }

    /**
     * Fonction de triage d'un tableau par la position par ordre croissant
     * @param $array1
     * @param $array2
     * @return int
     */
    private function cmp_medias($array1, $array2) {
        if ($array1['position'] == $array2['position']) {
            return 0;
        }
        return ($array1['position'] < $array2['position']) ? -1 : 1;
    }

    /**
     * Fonction qui retourne un tableau de médias classés par ordre croissant de la position obtenue via l'url
     * @param array $medias
     * @return array
     */
    private function orderMedias(array $medias){
        $tempMedias = [];
        foreach ($medias as $currentMedia){
            $stringReplaced = preg_replace('/^.*?_(.*?)\..*$/','$1$2',$currentMedia['url']);
            if ($stringReplaced === ''){
                return $medias;
            }
            $tempMedias[] = [
                'position' => $stringReplaced,
                'url' => $currentMedia['url'],
                'updatedAt'=> $currentMedia['updatedAt']
            ];
        }
        if (usort($tempMedias, array('App\Controller\AdminApiController', 'cmp_medias'))){
            return $tempMedias;
        }
        return $medias;
    }


    /**
     * Create taxon
     *
     * @param Request $request
     * @param ImageUploaderInterface $imageUploader
     * @param TaxonRepository $taxonRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function createTaxon(Request $request, ImageUploaderInterface $imageUploader, TaxonRepository $taxonRepository, EntityManagerInterface $entityManager){

        $syncedAt = new DateTime();

        if( !$taxon = $taxonRepository->findOneByCode($request->get('code') ) )
            $taxon = new Taxon();

        $form = $this->submitForm(TaxonType::class, $request, $taxon);

        if( !$form->isValid() )
            return $this->json($this->getErrors($form), 500);

        $this->processMedias($syncedAt, $taxon, $imageUploader);
        $this->persist($taxon, $entityManager);

        return $this->json($taxonRepository->hydrate($taxon));
    }


    /**
     * Update taxon
     *
     * @param $code
     * @param Request $request
     * @param ImageUploaderInterface $imageUploader
     * @param TaxonRepository $taxonRepository
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse
     */
    public function updateTaxon($code, Request $request, ImageUploaderInterface $imageUploader, TaxonRepository $taxonRepository, EntityManagerInterface $entityManager){

        $syncedAt = new DateTime();

        if( !$taxon = $taxonRepository->findOneByCode($code) )
            return $this->json('Taxon does not exists', 500);

        $form = $this->submitForm(TaxonType::class, $request, $taxon);

        if( !$form->isValid() )
            return $this->json($this->getErrors($form));

        $this->processMedias($syncedAt, $taxon, $imageUploader);
        $this->persist($taxon, $entityManager);

        return $this->json($taxonRepository->hydrate($taxon));
    }
}
