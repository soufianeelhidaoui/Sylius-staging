<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;


abstract class AbstractController extends SymfonyAbstractController
{
    /**
     * @param Request $request
     * @return array
     */
    protected function getPagination(Request $request){

        $limit = max(2, min(200, intval($request->get('limit', $_ENV['DEFAULT_LIMIT']??10))));
        $offset = max(0, intval($request->get('offset', 0)));

        return [$limit, $offset];
    }


    /**
     * @param $formType
     * @param Request $request
     * @param $entity
     * @param bool $clearMissing
     * @param array $options
     * @return FormInterface
     */
    protected function submitForm($formType, $data, $entity=null, $clearMissing=true, $options=[])
    {
        $options = array_merge(['allow_extra_fields'=>true], $options);

        $form = $this->createForm($formType, $entity, $options);

		if( $data instanceof Request ){

			$request = $data;

			if( $request->getMethod() == 'GET'){

				$data = $request->query->all();
			}
			else{
				$data = $request->request->all();
				$data = array_merge($data, $request->files->all());
			}
		}

        $form->submit($data, $clearMissing);

        return $form;
    }
}
