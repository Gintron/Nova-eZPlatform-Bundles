<?php
/**
 * NovaeZMailingBundle Bundle.
 *
 * @package   Novactive\Bundle\eZMailingBundle
 *
 * @author    Novactive <s.morel@novactive.com>
 * @copyright 2018 Novactive
 * @license   https://github.com/Novactive/NovaeZMailingBundle/blob/master/LICENSE MIT Licence
 */
declare(strict_types=1);

namespace Novactive\Bundle\eZMailingBundle\Controller\Admin;

use Doctrine\ORM\EntityManagerInterface;
use Novactive\Bundle\eZMailingBundle\Entity\Broadcast;
use Novactive\Bundle\eZMailingBundle\Entity\MailingList;
use Novactive\Bundle\eZMailingBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Class DashboardController.
 */
class DashboardController extends Controller
{
    /**
     * @Route("/", name="novaezmailing_dashboard_index")
     * @Template()
     *
     * @return Response
     */
    public function indexAction(EntityManagerInterface $entityManager): array
    {
        $repoBroadcast = $entityManager->getRepository(Broadcast::class);
        $repoUsers     = $entityManager->getRepository(User::class);

        $translator = $this->container->get('translator');
        $aUsers = $repoUsers->findLastUpdated();
        foreach($aUsers as $user){
            switch($user->getStatus()){

                case $user::PENDING :
                    $translatedTag =  $translator->trans('dashboard.users.status.pending', array(), 'ezmailing');
                    $user->statusStyle = $user::STATUSES_STYLE[$user::PENDING];
                    break;

                case $user::CONFIRMED :
                    $translatedTag =  $translator->trans('dashboard.users.status.confirmed', array(), 'ezmailing');
                    $user->statusStyle = $user::STATUSES_STYLE[$user::CONFIRMED];
                    break;

                case $user::SOFT_BOUNCE :
                    $translatedTag =  $translator->trans('dashboard.users.status.soft_bounce', array(), 'ezmailing');
                    $user->statusStyle = $user::STATUSES_STYLE[$user::SOFT_BOUNCE];
                    break;

                case $user::HARD_BOUNCE :
                    $translatedTag =  $translator->trans('dashboard.users.status.hard_bounce', array(), 'ezmailing');
                    $user->statusStyle = $user::STATUSES_STYLE[$user::HARD_BOUNCE];
                    break;

                case $user::BLACKLISTED :
                    $translatedTag =  $translator->trans('dashboard.users.status.blacklisted', array(), 'ezmailing');
                    $user->statusStyle = $user::STATUSES_STYLE[$user::BLACKLISTED];
                    break;

                default:
                    break;
            }

            /** @var User $user */

            $user->setStatus($translatedTag);

        }

        return [
            'broadcasts' => $repoBroadcast->findLastBroadcasts(),
            'users'      => $repoUsers->findLastUpdated(),
        ];
    }

    /**
     * @Route("/search/autocomplete", name="novaezmailing_dashboard_search_autocomplete")
     *
     * @param Request                $request
     * @param RouterInterface        $router
     * @param EntityManagerInterface $entityManager
     *
     * @return JsonResponse
     */
    public function autocompleteSearchAction(
        Request $request,
        RouterInterface $router,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse('Not Authorized', 403);
        }

        $query = $request->query->get('query');

        $repo  = $entityManager->getRepository(User::class);
        $users = $repo->findByFilters(['query' => $query]);

        $userResults = array_map(
            function (User $user) use ($router) {
                return [
                    'value' => trim($user->getGender().' '.$user->getFirstName().' '.$user->getLastName()),
                    'data'  => $router->generate('novaezmailing_user_show', ['user' => $user->getId()]),
                ];
            },
            $users
        );

        $repo               = $entityManager->getRepository(MailingList::class);
        $mailingLists       = $repo->findByFilters(['query' => $query]);
        $mailingListResults = array_map(
            function (MailingList $mailingList) use ($router) {
                return [
                    'value' => trim($mailingList->getName()),
                    'data'  => $router->generate(
                        'novaezmailing_mailinglist_show',
                        ['mailingList' => $mailingList->getId()]
                    ),
                ];
            },
            $mailingLists
        );

        return new JsonResponse(['suggestions' => $userResults + $mailingListResults]);
    }
}
