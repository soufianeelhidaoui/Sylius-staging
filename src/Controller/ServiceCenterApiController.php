<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order\Order;
use App\Form\OrderListType;
use App\Form\OrderUpdateType;
use App\Form\TermUpdateType;
use App\Repository\OrderRepository;
use Dflydev\DotAccessData\Data;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;

class ServiceCenterApiController extends AbstractController
{
    /**
     * @param FormInterface $form
     * @return array
     */
    protected function getErrors(FormInterface $form)
    {
        $errors = array();

        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface) {
                if ($childErrors = $this->getErrors($childForm)) {
                    $errors[$childForm->getName()] = $childErrors;
                }
            }
        }

        return $errors;
    }

    /**
     * Get order list
     *
     * @param string $service_center_id
     * @param Request $request
     * @param OrderRepository $orderRepository
     * @return JsonResponse
     */
    public function getOrders(string $service_center_id, Request $request, OrderRepository $orderRepository){

        $form = $this->submitForm(OrderListType::class, $request);

        if ( !$form->isValid() )
            return $this->json(['error'=>$this->getErrors($form)], 500);

        list($limit, $offset) = $this->getPagination($request);

        $criteria = $form->getData();
        $criteria['service_center_id'] = $service_center_id;
        $criteria['paymentState'] = 'paid';

        $sortBy = $request->get('order') ? [$request->get('sort')=>$request->get('order')] : [];

        $orders = $orderRepository->findBy($criteria, $sortBy, $limit, $offset);

        $data = [];

        foreach ($orders as $order)
            $data[] = $orderRepository->hydrate($order);

        return $this->json([
	        'items'=>$data,
	        'count'=>count($orders)
        ]);
    }

    /**
     * Get order
     *
     * @param string $service_center_id
     * @param $order_id
     * @param OrderRepository $orderRepository
     * @return JsonResponse
     */
    public function getOrder(string $service_center_id, $order_id, OrderRepository $orderRepository){

        if( !$order = $orderRepository->findOneBy(['id'=>$order_id, 'service_center_id'=>$service_center_id]) )
            return $this->json(['message'=>'Order not found'], 500);

        $data = $orderRepository->hydrate($order);

        return $this->json($data);
    }

    /**
     * Update order
     *
     * @param string $service_center_id
     * @param int $order_id
     * @param Request $request
     * @param KernelInterface $kernel
     * @param OrderRepository $orderRepository
     * @param EntityManagerInterface $entityManager
     * @param Swift_Mailer $mailer
     * @return JsonResponse
     */
    public function updateOrder(string $service_center_id, int $order_id, Request $request, KernelInterface $kernel, OrderRepository $orderRepository, EntityManagerInterface $entityManager, Swift_Mailer $mailer){

        $form = $this->submitForm(OrderUpdateType::class, $request);

        if ( !$form->isValid() )
            return $this->json(['error'=>$this->getErrors($form)], 500);

        $criteria = $form->getData();
        $error = false;

        /** @var Order $order */
        if( !$order = $orderRepository->findOneBy(['id'=>$order_id, 'service_center_id'=>$service_center_id]) )
            return $this->json(['message'=>'Order not found'], 500);

        if( $criteria['state']??false ){

            if( $order->getState() != $criteria['state']){

                $order->setState($criteria['state']);
            }
        }

        if( $criteria['notes']??false )
            $order->setNotes($criteria['notes']);

        $entityManager->persist($order);
        $entityManager->flush();

        $data = $orderRepository->hydrate($order);
        $data['error'] = $error;

        return $this->json($data);
    }

    /**
     * Get orders stats
     *
     * @param string $service_center_id
     * @param OrderRepository $orderRepository
     * @return JsonResponse
     */
    public function getOrdersStats(string $service_center_id, OrderRepository $orderRepository){

        $data = [
            'new'=>$orderRepository->getStats($service_center_id, Order::STATE_NEW),
            'confirmed'=>$orderRepository->getStats($service_center_id, Order::STATE_CONFIRMED),
            'fulfilled'=>$orderRepository->getStats($service_center_id, Order::STATE_FULFILLED),
            'completed'=>$orderRepository->getStats($service_center_id, Order::STATE_COMPLETED),
            'cancelled'=>$orderRepository->getStats($service_center_id, Order::STATE_CANCELLED)
        ];

        return $this->json($data);
    }

    /**
     * Update prices
     *
     * @param string $service_center_id
     * @param Request $request
     * @param KernelInterface $kernel
     * @return JsonResponse
     */
    public function updatePrices(string $service_center_id, Request $request, KernelInterface $kernel){

        $prices = $request->request->all();

        $filesystem = new Filesystem();
        $priceDir = $kernel->getProjectDir().'/private/service_center/prices/';

        if( empty($prices) )
            return $this->json(['message'=>'Invalid data'], 500);

        if( $filesystem->exists($priceDir) )
            $filesystem->mkdir($priceDir);

        $filesystem->dumpFile($priceDir.$service_center_id.'.json', json_encode($prices));

        return $this->json(['message'=>'Prices file imported', 'count' =>count($prices)]);
    }

    /**
     * Update terms
     *
     * @param string $service_center_id
     * @param Request $request
     * @param KernelInterface $kernel
     * @return JsonResponse
     */
    public function updateTerms(string $service_center_id, Request $request, KernelInterface $kernel){

        $filesystem = new Filesystem();
        $termDir = $kernel->getProjectDir().'/private/service_center/terms/';

        if( $filesystem->exists($termDir) )
            $filesystem->mkdir($termDir);

        $form = $this->submitForm(TermUpdateType::class, $request);

        if( !$form->isValid() )
            return $this->json(['error'=>$this->getErrors($form)], 500);

        /** @var UploadedFile $file */
        if( !$file = $form->get('file')->getData() )
            return $this->json(['error'=>'File not found'], 500);


        if( !$file->move($termDir, $service_center_id.'.'.$file->getClientOriginalExtension()) )
            return $this->json(['error'=>'File not uploaded'], 500);

        return $this->json(['message'=>'Terms file imported']);
    }

    /**
     * Get prices
     *
     * @param string $service_center_id
     * @param KernelInterface $kernel
     * @return JsonResponse
     */
    public function getPrices(string $service_center_id, KernelInterface $kernel){

        $filesystem = new Filesystem();
        $priceDir = $kernel->getProjectDir().'/private/service_center/prices/';

        $prices_file = $priceDir.$service_center_id.'.json';

        if( !$filesystem->exists($prices_file) )
            return $this->json(['message'=>'Prices file does not exists'], 500);

        $prices = json_decode(file_get_contents($prices_file), true);

        return $this->json($prices);
    }

    /**
     * Get terms
     *
     * @param string $service_center_id
     * @param KernelInterface $kernel
     * @return BinaryFileResponse|JsonResponse
     */
    public function getTerms(string $service_center_id, KernelInterface $kernel){

        $filesystem = new Filesystem();
        $priceDir = $kernel->getProjectDir().'/private/service_center/terms/';

        foreach(['docx','doc','pdf'] as $ext){

            $terms_file = $priceDir.$service_center_id.'.'.$ext;

            if( $filesystem->exists($terms_file) )
                return $this->file($terms_file);
        }

        return $this->json(['message'=>'Terms file does not exists'], 500);
    }

}
