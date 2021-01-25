<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\DTO\RegistrationDTO;
use App\Form\Handler\RegistrationHandler;
use App\Form\Type\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twig\Environment;

class RegisterController
{
    private FormFactoryInterface $formFactory;
    private Environment $twig;
    private EntityManagerInterface $em;
    private UrlGeneratorInterface $router;
    private UserPasswordEncoderInterface $encoder;
    private RegistrationHandler $registrationHandler;

    public function __construct(
        FormFactoryInterface $formFactory,
        Environment $twig,
        UrlGeneratorInterface $router,
        RegistrationHandler $registrationHandler
    ) {
        $this->formFactory = $formFactory;
        $this->twig = $twig;
        $this->router = $router;
        $this->registrationHandler = $registrationHandler;
    }

    /**
     * @Route("/register", name="register", methods={"GET", "POST"})
     */
    public function __invoke(Request $request, Session $session): Response
    {
        $dto = new RegistrationDTO();
        $form = $this->formFactory->create(RegisterType::class, $dto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            ($this->registrationHandler)($dto);

            $session->getFlashBag()->add('success', 'Bienvenue sur Twittlior !');

            return new RedirectResponse($this->router->generate('home'), 302);
        }

        return new Response($this->twig->render('register.html.twig', [
            'form' => $form->createView(),
        ]), $form->getErrors(true, true)->count() ? 400 : 200);
    }
}