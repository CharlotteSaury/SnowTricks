<?php

namespace App\Controller;

use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use App\Repository\TrickRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SitemapController extends AbstractController
{
    /**
     * @Route("/sitemap.xml", name="sitemap", defaults={"_format"="xml"})
     */
    public function index(Request $request, TrickRepository $trickRepository, UserRepository $userRepository, GroupRepository $groupRepository)
    {
        $hostname = $request->getSchemeAndHttpHost();

        $urls = [];
        $urls[] = ['loc' => $this->generateUrl('trick.index')];
        $urls[] = ['loc' => $this->generateUrl('app_login')];
        $urls[] = ['loc' => $this->generateUrl('app_register')];
        $urls[] = ['loc' => $this->generateUrl('app_forgotten_password')];
        $urls[] = ['loc' => $this->generateUrl('app_privacy')];
        $urls[] = ['loc' => $this->generateUrl('app_legal')];
        $urls[] = ['loc' => $this->generateUrl('user.trick.new')];
        $urls[] = ['loc' => $this->generateUrl('admin.users')];
        $urls[] = ['loc' => $this->generateUrl('admin.stats')];
        $urls[] = ['loc' => $this->generateUrl('group.index')];
        $urls[] = ['loc' => $this->generateUrl('user.tricks')];
        $urls[] = ['loc' => $this->generateUrl('user.comments')];

        foreach ($trickRepository->findBy(['parentTrick' => null]) as $trick) {
            $images = [
                'loc' => '/public/media/uploads/trick_' . $trick->getId() . '/' . $trick->getMainImage(),
                'title' => $trick->getName()
            ];
            foreach ($trick->getImages() as $image) {
                $images[] = [
                    'loc' => '/public/media/uploads/trick_' . $trick->getId() . '/' . $image->getName(),
                    'title' => $trick->getName()
                ];
            }
            $urls[] = [
                'loc' => $this->generateUrl('trick.show', [
                    'id' => $trick->getId(),
                    'slug' => $trick->getSlug()
                ]),
                'lastmod' => $trick->getUpdatedAt()->format('Y-m-d'),
                'images' => $images
            ];

            $routes = ['user.trick.edit', 'user.trick.report'];
            foreach ($routes as $route) {
                $urls[] = [
                    'loc' => $this->generateUrl($route, [
                        'id' => $trick->getId()
                    ])
                ];
            }
        }

        foreach ($userRepository->findAll() as $user) {
            $routes = ['user.dashboard', 'user.profile', 'user.edit'];
            foreach ($routes as $route) {
                $urls[] = [
                    'loc' => $this->generateUrl($route, [
                        'username' => $user->getUsername()
                    ])
                ];
            }
        }

        foreach ($groupRepository->findAll() as $group) {
            $urls[] = [
                'loc' => $this->generateUrl('group.edit', [
                    'id' => $group->getId()
                ])
            ];
        }

        $response = new Response(
            $this->renderView('sitemap/index.html.twig', [
                'urls' => $urls,
                'hostname' => $hostname
            ]),
            200
        );

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
