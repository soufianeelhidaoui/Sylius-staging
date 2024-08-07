<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order\OrderItem;
use App\Entity\Product\ProductVariant;
use Dflydev\DotAccessData\Data;
use GuzzleHttp\Client;
use Swift_Mailer;
use Sylius\Bundle\CoreBundle\Mailer\Emails;
use Sylius\Bundle\ProductBundle\Doctrine\ORM\ProductVariantRepository;

use Sylius\Component\Mailer\Sender\SenderInterface;
use Sylius\Component\Order\Context\CartContextInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use GuzzleHttp\Psr7\Request;

use Aws\Credentials\Credentials;
use Aws\Signature\SignatureV4;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelInterface;

class ShopApiController extends AbstractController
{
    public function getDealersAction(KernelInterface $kernel){

        $filesystem = new Filesystem();
        $serviceCenterFile = $kernel->getProjectDir().'/private/service_center/list.json';

        if (!file_exists($serviceCenterFile) || time()-filemtime($serviceCenterFile) > 3600) {

            $url = getenv('PAYMENT_SERVICE_URL').'/get-dealers/'.$_ENV['BRAND'];
            [$content, $code] = $this->getApiServiceResponse($url, 'GET');

            if( $code == 200 )
                $filesystem->dumpFile($serviceCenterFile, json_encode($content));

            return $this->json($content, $code);
        }
        else{

            $response = $this->file($serviceCenterFile);
            $response->headers->set('Content-Type', 'application/json');
            return $response;
        }
    }

    public function getDealerTerms($id, KernelInterface $kernel){

        $filesystem = new Filesystem();
        $priceDir = $kernel->getProjectDir().'/private/service_center/terms/';

        foreach(['docx','doc','pdf'] as $ext){

            $terms_file = $priceDir.$id.'.'.$ext;

            if( $filesystem->exists($terms_file) )
                return $this->file($terms_file);
        }

        throw new NotFoundHttpException();
    }

    public function getDealerAction($service_center_id, KernelInterface $kernel){

        $filesystem = new Filesystem();
        $priceDir = $kernel->getProjectDir().'/private/service_center/prices/';

        $prices_file = $priceDir.$service_center_id.'.json';

        $prices = [];

        if( $filesystem->exists($prices_file) ){

            $data = json_decode(file_get_contents($prices_file), true);

            foreach ($data as $sku=>$_data)
                $prices[$sku] = $_data['vendorPriceTTC']*100;
        }

        $termsDir = $kernel->getProjectDir().'/private/service_center/terms/';

        $term = false;

        foreach(['docx','doc','pdf'] as $ext){

            $terms_file = $termsDir.$service_center_id.'.'.$ext;

            if( $filesystem->exists($terms_file) )
                $term = true;
        }

        $has_payment = false;

        $url = getenv('PAYMENT_SERVICE_URL').'/has-payment/'.$service_center_id;
        [$content, $code] = $this->getApiServiceResponse($url, 'GET');
        if($code==200)
            $has_payment = $content;

        return $this->json(['prices'=>$prices, 'has_term'=>$term, 'has_payment'=>$has_payment]);
    }

    private function getApiServiceResponse($url, $method, $data=[]){

        $access_key = getenv('AWS_ACCESS_KEY');
        $secret_key = getenv('AWS_SECRET_KEY');
        $region = getenv('AWS_REGION');

        $credentials = new Credentials($access_key, $secret_key);

        $client = new Client();
        $request = new Request($method, $url, ['content-type' => 'application/json'], json_encode($data));

        $s4 = new SignatureV4("execute-api", $region);
        $signedRequest = $s4->signRequest($request, $credentials);

        try {

            $response = $client->send($signedRequest);
	        $content = json_decode($response->getBody()->getContents(), true);
	        $code = 200;
        }
        catch (\Throwable $t) {

	        $content = $t->getMessage();
            $code = 500;
        }


        return [$content, $code];
    }

	/**
	 * @param HttpRequest $request
     * @param KernelInterface $kernel
     * @param CartContextInterface $cartContext
     * @param Swift_Mailer $mailer
	 * @param ProductVariantRepository $productVariantRepository
	 * @param SenderInterface $sender
	 * @return JsonResponse
	 */
    public function createLeadAction(HttpRequest $request, KernelInterface $kernel, CartContextInterface $cartContext, Swift_Mailer $mailer, ProductVariantRepository $productVariantRepository){

        $data = [
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'phone_number' => $request->get('phone_number'),
            'title' => $request->get('title','mr'),
            'accessories_details' => $request->get('accessories_details',[]),
            'email' => $request->get('email'),
            'dealer' => $request->get('dealer')
        ];

		$brand = $request->get('brand');

		$brand = $_ENV['BRAND'];

        $cart = $cartContext->getCart();

        $data['order'] = $cart;

        if( $data['dealer']??false ){

            $serviceCenterFile = $kernel->getProjectDir().'/private/service_center/list.json';

            if( file_exists($serviceCenterFile) ){

                $partners = json_decode(file_get_contents($serviceCenterFile), true);
                foreach ($partners as $partner){

                    if( $partner['kvps'] == $data['dealer'] ){

                        $data['partner'] = $partner;
                        break;
                    }
                }
            }
        }

        try{

            $translations = new Data(json_decode(file_get_contents(__DIR__.'/../../translations/'.$brand.'.json'), true));

            $message = (new \Swift_Message())
                ->setSubject($translations->get('email.title'))
                ->setFrom([$translations->get('email.from') => $translations->get('email.brand')])
                ->setTo($data['email']);

            $message->setBody($this->renderView('@SyliusShop/Email/orderSaved.html.twig', $data), 'text/html');

            return $this->json($mailer->send($message));
        }
        catch (\Throwable $t){

            return $this->json(['message'=>$t->getMessage()], 500);
        }
    }
}
