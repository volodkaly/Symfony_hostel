<?php

namespace App\Controller\Admin;

use App\Entity\Booking;
use App\Entity\Message;
use App\Entity\Review;
use App\Entity\Room;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        // return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:


        $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        return $this->redirect($adminUrlGenerator->setController(BookingCrudController::class)->generateUrl());
    }

    // Option 2. You can make your dashboard redirect to different pages depending on the user
    //
    // if ('jane' === $this->getUser()->getUsername()) {
    //     return $this->redirectToRoute('...');
    // }

    // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
    // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
    //
    // return $this->render('some/path/my-dashboard.html.twig');


    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Symfony Hostel');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        // Додаємо твої сутності
        yield MenuItem::section('Hostel Management');
        yield MenuItem::linkToCrud('Bookings', 'fas fa-calendar-check', Booking::class);
        yield MenuItem::linkToCrud('Rooms', 'fas fa-bed', Room::class);
        yield MenuItem::linkToCrud('Reviews', 'fas fa-bed', Review::class);
        yield MenuItem::linkToCrud('Messages', 'fas fa-bed', Message::class);

        yield MenuItem::section('Users');
        yield MenuItem::linkToCrud('Customers', 'fas fa-users', User::class);
    }
}
