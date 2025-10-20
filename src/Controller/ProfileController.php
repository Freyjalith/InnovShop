<?php

namespace App\Controller;

use App\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profil', name: 'app_profile')]
    #[IsGranted('ROLE_USER')]
    public function index(): Response
    {
        return $this->render('profile/index.html.twig');
    }

    #[Route('/profil/modifier', name: 'app_profile_edit')]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = $this->getUser();

        if ($request->isMethod('POST')) {
            $firstName = $request->request->get('firstName');
            $lastName = $request->request->get('lastName');
            $email = $request->request->get('email');
            $currentPassword = $request->request->get('currentPassword');
            $newPassword = $request->request->get('newPassword');
            $confirmPassword = $request->request->get('confirmPassword');

            // Validation des champs obligatoires
            if (empty($firstName) || empty($lastName) || empty($email)) {
                $this->addFlash('error', 'Tous les champs sont obligatoires.');
                return $this->render('profile/edit.html.twig');
            }

            // Vérifier si l'email existe déjà
            $existingUser = $entityManager->getRepository(get_class($user))->findOneBy(['email' => $email]);
            if ($existingUser && $existingUser !== $user) {
                $this->addFlash('error', 'Cet email est déjà utilisé.');
                return $this->render('profile/edit.html.twig');
            }

            // Mettre à jour les informations de base
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setEmail($email);

            // Gestion du changement de mot de passe
            if (!empty($newPassword)) {
                if (empty($currentPassword)) {
                    $this->addFlash('error', 'Veuillez saisir votre mot de passe actuel.');
                    return $this->render('profile/edit.html.twig');
                }

                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'Mot de passe actuel incorrect.');
                    return $this->render('profile/edit.html.twig');
                }

                if ($newPassword !== $confirmPassword) {
                    $this->addFlash('error', 'Les mots de passe ne correspondent pas.');
                    return $this->render('profile/edit.html.twig');
                }

                if (strlen($newPassword) < 6) {
                    $this->addFlash('error', 'Le mot de passe doit contenir au moins 6 caractères.');
                    return $this->render('profile/edit.html.twig');
                }

                $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            }

            $entityManager->flush();
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès !');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig');
    }

    #[Route('/profil/adresses', name: 'app_profile_addresses')]
    #[IsGranted('ROLE_USER')]
    public function addresses(): Response
    {
        $user = $this->getUser();
        return $this->render('profile/addresses.html.twig', [
            'addresses' => $user->getAddresses()
        ]);
    }

    #[Route('/profil/adresses/ajouter', name: 'app_profile_address_add')]
    #[IsGranted('ROLE_USER')]
    public function addAddress(Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $user = $this->getUser();
            
            $address = new Address();
            $address->setUser($user)
                ->setTitle($request->request->get('title'))
                ->setFirstName($request->request->get('firstName'))
                ->setLastName($request->request->get('lastName'))
                ->setStreet($request->request->get('street'))
                ->setPostalCode($request->request->get('postalCode'))
                ->setCity($request->request->get('city'))
                ->setCountry($request->request->get('country'))
                ->setPhone($request->request->get('phone'))
                ->setIsDefault($request->request->get('isDefault') === '1');

            if ($address->isDefault()) {
                foreach ($user->getAddresses() as $existingAddress) {
                    $existingAddress->setIsDefault(false);
                }
            }

            $entityManager->persist($address);
            $entityManager->flush();

            $this->addFlash('success', 'Adresse ajoutée avec succès !');
            return $this->redirectToRoute('app_profile_addresses');
        }

        return $this->render('profile/address_form.html.twig');
    }

    #[Route('/profil/adresses/{id}/modifier', name: 'app_profile_address_edit')]
    #[IsGranted('ROLE_USER')]
    public function editAddress(Address $address, Request $request, EntityManagerInterface $entityManager): Response
    {
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        if ($request->isMethod('POST')) {
            $address->setTitle($request->request->get('title'))
                ->setFirstName($request->request->get('firstName'))
                ->setLastName($request->request->get('lastName'))
                ->setStreet($request->request->get('street'))
                ->setPostalCode($request->request->get('postalCode'))
                ->setCity($request->request->get('city'))
                ->setCountry($request->request->get('country'))
                ->setPhone($request->request->get('phone'))
                ->setIsDefault($request->request->get('isDefault') === '1');

            if ($address->isDefault()) {
                foreach ($this->getUser()->getAddresses() as $existingAddress) {
                    if ($existingAddress !== $address) {
                        $existingAddress->setIsDefault(false);
                    }
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Adresse modifiée avec succès !');
            return $this->redirectToRoute('app_profile_addresses');
        }

        return $this->render('profile/address_form.html.twig', [
            'address' => $address
        ]);
    }

    #[Route('/profil/adresses/{id}/supprimer', name: 'app_profile_address_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function deleteAddress(Address $address, EntityManagerInterface $entityManager): Response
    {
        if ($address->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $entityManager->remove($address);
        $entityManager->flush();

        $this->addFlash('success', 'Adresse supprimée avec succès !');
        return $this->redirectToRoute('app_profile_addresses');
    }
}