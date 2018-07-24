import VueRouter from 'vue-router';
import AuthService from './services/auth-service';



let publicRoutes = [
    {
        path: '/',
        redirect: to => {
            if (AuthService.isLoggedIn) {
                return '/dashboard';
            }
            else {
                return '/login';
            }
        }
    },
    {
        path: '/login',
        component: require('./public-components/pages/login')
    },
    {
        path: '/forgot-password',
        component: require('./public-components/pages/forgot-password')
    },
    {
        path: '/password/reset/:token',
        component: require('./public-components/pages/reset-password')
    },
];

let privateRoutes = [
    {
        path: '/dashboard',
        component: require('./private-components/pages/dashboard'),
        meta: { requiresAuth: true },
        name: 'Dashboard'
    },
    {
        path: '/people-dashboard',
        component: require('./private-components/pages/people-dashboard'),
        meta: { requiresAuth: true },
        name: 'PeopleDashboard'
    },
    {
        path: '/address-details/:id',
        component: require('./private-components/pages/address-details'),
        meta: { requiresAuth: true },
        name: 'AddressDetails'
    },
    {
        path: '/user/edit-profile',
        component: require('./private-components/pages/edit-profile'),
        meta: { requiresAuth: true},
        name: 'Edit Profile'
    }
];

let adminRoutes = [
    {
        path: '/admin/users',
        component: require('./private-components/pages/admin/admin-users'),
        meta: { requiresAuth: true, adminAuth: true},
        name: 'AdminUsers'
    },
    {
        path: '/admin/activity',
        component: require('./private-components/pages/admin/admin-activities'),
        meta: { requiresAuth: true, adminAuth: true},
        name: 'AdminActivity'
    },
    {
        path: '/admin/configurations',
        component: require('./private-components/pages/admin/configurations'),
        meta: { requiresAuth: true, adminAuth: true},
        name: 'AdminConfigurations'
    },
    {
        path: '/admin/website-notifications',
        component: require('./private-components/pages/admin/website-notifications'),
        meta: { requiresAuth: true, adminAuth: true},
        name: 'AdminNotifications'
    },
];

const router = new VueRouter({
    routes: publicRoutes.concat(privateRoutes, adminRoutes),
    mode: 'history',
    linkActiveClass: 'active'
});

router.beforeEach((to, from, next) => {
    if (to.matched.some(record => record.meta.requiresAuth) && !AuthService.isLoggedIn) {
        next({ path: '/login'});
    }
    else if (to.path == '/login' && AuthService.isLoggedIn){
        next({ path: '/dashboard'});
    }
    else if (to.meta.adminAuth) {
        const authUser = JSON.parse(window.localStorage.getItem('logged-user'));

        if (authUser.role === 'admin') {
            next();
        } else {
            next({ path: '/dashboard' });
        }
    }
    else {
        next();
    }
});


export default router;
