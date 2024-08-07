<?php

namespace App\Service;


use Dflydev\DotAccessData\Data;
use Psr\Log\LoggerInterface;
use Sylius\Component\Core\Model\OrderInterface;
use Twig\Environment;

class MailAfterOrderService {

    private $mailer;
    private $templating;
    private $logger;

    public function __construct(\Swift_Mailer $mailer, Environment $templating, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->templating = $templating;
        $this->logger = $logger;
    }

    public function sendMailAfterOrder(OrderInterface $order){

        $translations = new Data(json_decode(file_get_contents(__DIR__.'/../../translations/'.$_ENV['BRAND'].'.json'),true));
        $mail = (new \Swift_Message())
            ->setSubject($translations->get('email.title'))
            ->setFrom([$translations->get('email.from') => $translations->get('email.brand')])
            ->setTo($order->getCustomer()->getEmail());

        try {
            $mail->setBody($this->templating->render('@SyliusShop/Email/orderConfirmation.html.twig', ['order' => $order]), 'text/html');
            $this->mailer->send($mail);
        } catch (\Throwable $t){
            $this->logger->critical('Error with confirmation mail for order N°'.$order->getNumber());
        }

    }
}
