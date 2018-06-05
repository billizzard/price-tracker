<?php

namespace App\Controller;

use App\Repository\FileRepository;
use App\Repository\HostRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class IndexController extends FrontendController
{
    public function indexAction(HostRepository $hostRepository, FileRepository $fileRepository)
    {
        $hosts = $hostRepository->getForHomepage();

        return $this->render('index/index.html.twig');
    }







//    public function index()
//    {
//
//        $this->addFlash(
//            'notice',
//            'Your changes were saved!'
//        );

//        // creates a CSS-response with a 200 status code
//        $response = new Response('<style> ... </style>');
//        $response->headers->set('Content-Type', 'text/css');


// returns '{"username":"jane.doe"}' and sets the proper Content-Type header
//        return $this->json(array('username' => 'jane.doe'));

//        $url = $this->generateUrl(
//            'blog_show',
//            array('slug' => 'my-blog-post')
//        );

//        // does a permanent - 301 redirect
//        return $this->redirectToRoute('homepage', array(), 301);
//
//        // redirects externally
//        return $this->redirect('http://symfony.com/doc');

//        if (!$product) {
//            throw $this->createNotFoundException('The product does not exist');
//        }

//            throw $this->createNotFoundException('The product does not exist');
//
//        return $this->render('index/index.html.twig', array(
//            'number' => 3455,
//        ));
        // replace this line with your own code!
        //return $this->render('@Maker/demoPage.html.twig', [ 'path' => str_replace($this->getParameter('kernel.project_dir').'/', '', __FILE__) ]);
//    }

//    public function fileAction()
//    {
//        // load the file from the filesystem
//        $file = new File('/path/to/some_file.pdf');
//
//        return $this->file($file);
//
//        // rename the downloaded file
//        return $this->file($file, 'custom_name.pdf');
//
//        // display the file contents in the browser instead of downloading it
//        return $this->file('invoice_3241.pdf', 'my_invoice.pdf', ResponseHeaderBag::DISPOSITION_INLINE);
//    }
}
