<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

final class UserController extends AbstractController
{
    #[Route('/admin/user/list', name: 'app_user_list')]
    public function index(EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();
        return $this->render('user/list.html.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/admin/user/{id}/toggle-role/{role}', name: 'app_user_toggle_role', methods: ['POST'])]
    public function toggleRole(User $user, string $role, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Liste des rôles autorisés pour éviter les injections
        $allowedRoles = [
            'ROLE_ADMIN',
            'ROLE_GESTIONNAIRE',
            'ROLE_BLOC',
            'ROLE_POULAILLER',
            'ROLE_SALLE'
        ];

        if (!in_array($role, $allowedRoles)) {
            $this->addFlash('danger', 'Rôle invalide.');
            return $this->redirectToRoute('app_user_index');
        }

        if ($this->isCsrfTokenValid('toggle_role_' . $user->getId() . '_' . $role, $request->request->get('_token'))) {
            $roles = $user->getRoles();

            if (in_array($role, $roles)) {
                // Retirer le rôle
                $roles = array_diff($roles, [$role]);
                $this->addFlash('warning', sprintf('Le rôle %s a été retiré.', $role));
            } else {
                // Ajouter le rôle
                $roles[] = $role;
                $this->addFlash('success', sprintf('Le rôle %s a été attribué.', $role));
            }

            $user->setRoles(array_unique($roles));
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_list');
    }

    #[Route('/admin/user/edit/{id}', name: 'app_user_edit')]
    public function edit(EntityManagerInterface $em, Request $request, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }

        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_user_list');
        }

        return $this->render('user/index.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/admin/user/delete/{id}', name: 'app_user_delete')]
    public function delete(EntityManagerInterface $em, int $id): Response
    {
        $user = $em->getRepository(User::class)->find($id);
        if (!$user) {
            return $this->redirectToRoute('app_user_list');
        }

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute('app_user_list');
    }
}
