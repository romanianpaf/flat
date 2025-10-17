import { createRouter, createWebHistory } from "vue-router";
import Default from "../views/dashboards/Default.vue";
import Automotive from "../views/dashboards/Automotive.vue";
import SmartHome from "../views/dashboards/SmartHome.vue";
import VRDefault from "../views/dashboards/vr/VRDefault.vue";
import VRInfo from "../views/dashboards/vr/VRInfo.vue";
import CRM from "../views/dashboards/CRM.vue";
import Overview from "../views/pages/profile/Overview.vue";
import Teams from "../views/pages/profile/Teams.vue";
import Projects from "../views/pages/profile/Projects.vue";
import General from "../views/pages/projects/General.vue";
import Timeline from "../views/pages/projects/Timeline.vue";
import NewProject from "../views/pages/projects/NewProject.vue";
import Pricing from "../views/pages/Pricing.vue";
import RTL from "../views/pages/Rtl.vue";
import Charts from "../views/pages/Charts.vue";
import SweetAlerts from "../views/pages/SweetAlerts.vue";
import Notifications from "../views/pages/Notifications.vue";
import Kanban from "../views/applications/Kanban.vue";
import Wizard from "../views/applications/wizard/Wizard.vue";
import DataTables from "../views/applications/DataTables.vue";
import Calendar from "../views/applications/Calendar.vue";
import Analytics from "../views/applications/analytics/Analytics.vue";
import EcommerceOverview from "../views/ecommerce/overview/Overview.vue";
import NewProduct from "../views/ecommerce/products/NewProduct.vue";
import EditProduct from "../views/ecommerce/EditProduct.vue";
import ProductPage from "../views/ecommerce/ProductPage.vue";
import ProductsList from "../views/ecommerce/ProductsList.vue";
import OrderDetails from "../views/ecommerce/Orders/OrderDetails";
import OrderList from "../views/ecommerce/Orders/OrderList";
import Referral from "../views/ecommerce/Referral";
import Reports from "../views/pages/Users/Reports.vue";
import NewUserCT from "../views/pages/Users/NewUserCT.vue";
import Settings from "../views/pages/Account/Settings.vue";
import Billing from "../views/pages/Account/Billing.vue";
import Invoice from "../views/pages/Account/Invoice.vue";
import Security from "../views/pages/Account/Security.vue";
import Widgets from "../views/pages/Widgets.vue";
import Basic from "../views/auth/signin/Basic.vue";
import Cover from "../views/auth/signin/Cover.vue";
import Login from "../views/auth/signin/Login.vue";
import Illustration from "../views/auth/signin/Illustration.vue";
import ResetBasic from "../views/auth/reset/Basic.vue";
import ResetCover from "../views/auth/reset/Cover.vue";
import SendEmail from "../views/auth/reset/SendEmail.vue";
import ResetPassword from "../views/auth/reset/ResetPassword.vue";
import ResetIllustration from "../views/auth/reset/Illustration.vue";
import VerificationBasic from "../views/auth/verification/Basic.vue";
import VerificationCover from "../views/auth/verification/Cover.vue";
import VerificationIllustration from "../views/auth/verification/Illustration.vue";
import SignupBasic from "../views/auth/signup/Basic.vue";
import SignupCover from "../views/auth/signup/Cover.vue";
import Register from "../views/auth/signup/Register.vue";
import SignupIllustration from "../views/auth/signup/Illustration.vue";
import Error404 from "../views/auth/error/Error404.vue";
import Error500 from "../views/auth/error/Error500.vue";
import lockBasic from "../views/auth/lock/Basic.vue";
import lockCover from "../views/auth/lock/Cover.vue";
import lockIllustration from "../views/auth/lock/Illustration.vue";
import Profile from "../views/examples/Profile.vue";
import UserProfile from "../views/Profile.vue";
import UserSettings from "../views/Settings.vue";
import Roles from "../views/examples/Roles/Roles.vue";
import NewRole from "../views/examples/Roles/NewRole.vue";
import EditRole from "../views/examples/Roles/EditRole.vue";
import RolePermissions from "../views/examples/Roles/RolePermissions.vue";
import Users from "../views/examples/Users/Users.vue";
import NewUser from "../views/examples/Users/NewUser.vue";
import EditUser from "../views/examples/Users/EditUser.vue";
import Tags from "../views/examples/Tags/Tags.vue";
import NewTag from "../views/examples/Tags/NewTag.vue";
import EditTag from "../views/examples/Tags/EditTag.vue";
import Tenants from "../views/examples/Tenants/Tenants.vue";
import NewTenant from "../views/examples/Tenants/NewTenant.vue";
import EditTenant from "../views/examples/Tenants/EditTenant.vue";
import Automations from "../views/examples/Automations/Automations.vue";
import NewAutomation from "../views/examples/Automations/NewAutomation.vue";
import EditAutomation from "../views/examples/Automations/EditAutomation.vue";
import Polls from "../views/examples/Polls/Polls.vue";
import NewPoll from "../views/examples/Polls/NewPoll.vue";
import EditPoll from "../views/examples/Polls/EditPoll.vue";
import UserVoices from "../views/examples/UserVoices/UserVoices.vue";
import NewUserVoice from "../views/examples/UserVoices/NewUserVoice.vue";
import EditUserVoice from "../views/examples/UserVoices/EditUserVoice.vue";
import Categories from "../views/examples/Categories/Categories.vue";
import NewCategory from "../views/examples/Categories/NewCategory.vue";
import EditCategory from "../views/examples/Categories/EditCategory.vue";
import Items from "../views/examples/Items/Items.vue";
import NewItem from "../views/examples/Items/NewItem.vue";
import EditItem from "../views/examples/Items/EditItem.vue";
import ServiceCategories from "../views/examples/Services/ServiceCategories.vue";
import NewServiceCategory from "../views/examples/Services/NewServiceCategory.vue";
import EditServiceCategory from "../views/examples/Services/EditServiceCategory.vue";
import ServiceSubcategories from "../views/examples/Services/ServiceSubcategories.vue";
import NewServiceSubcategory from "../views/examples/Services/NewServiceSubcategory.vue";
import EditServiceSubcategory from "../views/examples/Services/EditServiceSubcategory.vue";
import ServiceProviders from "../views/examples/Services/ServiceProviders.vue";
import NewServiceProvider from "../views/examples/Services/NewServiceProvider.vue";
import EditServiceProvider from "../views/examples/Services/EditServiceProvider.vue";
import admin from "../middlewares/admin.js";
import adminCreator from "../middlewares/admin_creator.js";
import guest from "../middlewares/guest.js";
import auth from "../middlewares/auth.js";

const routes = [
  {
    path: "/",
    name: "/",
    redirect: "/dashboards/dashboard-default",
  },
  {
    path: "/dashboards/dashboard-default",
    name: "Default",
    component: Default,
  },
  {
    path: "/dashboards/automotive",
    name: "Automotive",
    component: Automotive,
  },
  {
    path: "/dashboards/smart-home",
    name: "Smart Home",
    component: SmartHome,
  },
  {
    path: "/dashboards/vr/vr-default",
    name: "VR Default",
    component: VRDefault,
  },
  {
    path: "/dashboards/vr/vr-info",
    name: "VR Info",
    component: VRInfo,
  },
  {
    path: "/dashboards/crm",
    name: "CRM",
    component: CRM,
  },
  {
    path: "/pages/profile/overview",
    name: "Profile Overview",
    component: Overview,
  },
  {
    path: "/pages/profile/teams",
    name: "Teams",
    component: Teams,
  },
  {
    path: "/pages/profile/projects",
    name: "All Projects",
    component: Projects,
  },
  {
    path: "/pages/projects/general",
    name: "General",
    component: General,
  },
  {
    path: "/pages/projects/timeline",
    name: "Timeline",
    component: Timeline,
  },
  {
    path: "/pages/projects/new-project",
    name: "New Project",
    component: NewProject,
  },
  {
    path: "/pages/pricing-page",
    name: "Pricing Page",
    component: Pricing,
  },
  {
    path: "/pages/rtl-page",
    name: "RTL",
    component: RTL,
  },
  {
    path: "/pages/charts",
    name: "Charts",
    component: Charts,
  },
  {
    path: "/pages/widgets",
    name: "Widgets",
    component: Widgets,
  },
  {
    path: "/pages/sweet-alerts",
    name: "Sweet Alerts",
    component: SweetAlerts,
  },
  {
    path: "/pages/notifications",
    name: "Notifications",
    component: Notifications,
  },
  {
    path: "/applications/kanban",
    name: "Kanban",
    component: Kanban,
  },
  {
    path: "/applications/wizard",
    name: "Wizard",
    component: Wizard,
  },
  {
    path: "/applications/data-tables",
    name: "Data Tables",
    component: DataTables,
  },
  {
    path: "/applications/calendar",
    name: "Calendar",
    component: Calendar,
  },
  {
    path: "/applications/analytics",
    name: "Analytics",
    component: Analytics,
  },
  {
    path: "/ecommerce/overview",
    name: "Overview",
    component: EcommerceOverview,
  },
  {
    path: "/ecommerce/products/new-product",
    name: "New Product",
    component: NewProduct,
  },
  {
    path: "/ecommerce/products/edit-product",
    name: "Edit Product",
    component: EditProduct,
  },
  {
    path: "/ecommerce/products/product-page",
    name: "Product Page",
    component: ProductPage,
  },
  {
    path: "/ecommerce/products/products-list",
    name: "Products List",
    component: ProductsList,
  },
  {
    path: "/ecommerce/Orders/order-details",
    name: "Order Details",
    component: OrderDetails,
  },
  {
    path: "/ecommerce/Orders/order-list",
    name: "Order List",
    component: OrderList,
  },
  {
    path: "/ecommerce/referral",
    name: "Referral",
    component: Referral,
  },
  {
    path: "/pages/users/reports",
    name: "Reports",
    component: Reports,
  },
  {
    path: "/pages/users/new-user",
    name: "New User CT",
    component: NewUserCT,
  },
  {
    path: "/pages/account/settings",
    name: "Settings",
    component: Settings,
  },
  {
    path: "/pages/account/billing",
    name: "Billing",
    component: Billing,
  },
  {
    path: "/pages/account/invoice",
    name: "Invoice",
    component: Invoice,
  },
  {
    path: "/pages/account/Security",
    name: "Security",
    component: Security,
  },
  {
    path: "/authentication/signin/basic",
    name: "Signin Basic",
    component: Basic,
  },
  {
    path: "/authentication/signin/cover",
    name: "Signin Cover",
    component: Cover,
  },
  {
    path: "/authentication/signin/illustration",
    name: "Signin Illustration",
    component: Illustration,
  },
  {
    path: "/authentication/reset/basic",
    name: "Reset Basic",
    component: ResetBasic,
  },
  {
    path: "/authentication/reset/cover",
    name: "Reset Cover",
    component: ResetCover,
  },
  {
    path: "/authentication/reset/illustration",
    name: "Reset Illustration",
    component: ResetIllustration,
  },
  {
    path: "/authentication/lock/basic",
    name: "Lock Basic",
    component: lockBasic,
  },
  {
    path: "/authentication/lock/cover",
    name: "Lock Cover",
    component: lockCover,
  },
  {
    path: "/authentication/lock/illustration",
    name: "Lock Illustration",
    component: lockIllustration,
  },
  {
    path: "/authentication/verification/basic",
    name: "Verification Basic",
    component: VerificationBasic,
  },
  {
    path: "/authentication/verification/cover",
    name: "Verification Cover",
    component: VerificationCover,
  },
  {
    path: "/authentication/verification/illustration",
    name: "Verification Illustration",
    component: VerificationIllustration,
  },
  {
    path: "/authentication/signup/basic",
    name: "Signup Basic",
    component: SignupBasic,
  },
  {
    path: "/authentication/signup/cover",
    name: "Signup Cover",
    component: SignupCover,
  },
  {
    path: "/authentication/signup/illustration",
    name: "Signup Illustration",
    component: SignupIllustration,
  },
  {
    path: "/authentication/error/error404",
    name: "Error Error404",
    component: Error404,
  },
  {
    path: "/authentication/error/error500",
    name: "Error Error500",
    component: Error500,
  },
  {
    path: "/login",
    name: "Login",
    component: Login,
    meta: {
      middleware: [guest],
    },
  },
  {
    path: "/register",
    name: "Register",
    component: Register,
    meta: {
      middleware: [guest],
    },
  },
  {
    path: "/password/email",
    name: "SendEmail",
    component: SendEmail,
    meta: {
      middleware: [guest],
    },
  },
  {
    path: "/password/reset",
    name: "ResetPassword",
    component: ResetPassword,
    meta: {
      middleware: [guest],
    },
  },
  {
    path: "/examples/profile",
    name: "Profile",
    component: Profile,
  },
  {
    path: "/roles/list",
    name: "Roles",
    component: Roles,
    meta: {
      middleware: [auth, admin],
    },
  },
  {
    path: "/roles/new",
    name: "New Role",
    component: NewRole,
    meta: {
      middleware: [auth, admin],
    },
  },
  {
    path: "/roles/edit/:id",
    name: "Edit Role",
    component: EditRole,
    meta: {
      middleware: [auth, admin],
    },
  },
  {
    path: "/roles/:id/permissions",
    name: "Role Permissions",
    component: RolePermissions,
    meta: {
      middleware: [auth, admin],
    },
  },
  {
    path: "/profile",
    name: "Profilul Utilizatorului",
    component: UserProfile,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/settings",
    name: "Configurări Utilizator",
    component: UserSettings,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/users/list",
    name: "Users",
    component: Users,
    meta: {
      middleware: [auth, admin],
    },
  },
  {
    path: "/users/new",
    name: "New User",
    component: NewUser,
    meta: {
      middleware: [auth, admin],
    },
  },
  {
    path: "/users/edit/:id",
    name: "Edit User",
    component: EditUser,
    meta: {
      middleware: [auth, admin],
    },
  },
  {
    path: "/tags/list",
    name: "Tags",
    component: Tags,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/tags/new",
    name: "New Tag",
    component: NewTag,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/tags/edit/:id",
    name: "Edit Tag",
    component: EditTag,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/tenants/list",
    name: "Tenants",
    component: Tenants,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/tenants/new",
    name: "New Tenant",
    component: NewTenant,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/tenants/edit/:id",
    name: "Edit Tenant",
    component: EditTenant,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/automations/list",
    name: "Automations",
    component: Automations,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/automations/new",
    name: "New Automation",
    component: NewAutomation,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/automations/edit/:id",
    name: "Edit Automation",
    component: EditAutomation,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/polls/list",
    name: "Polls",
    component: Polls,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/polls/new",
    name: "New Poll",
    component: NewPoll,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/polls/edit/:id",
    name: "Edit Poll",
    component: EditPoll,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/user-voices/list",
    name: "User Voices",
    component: UserVoices,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/user-voices/new",
    name: "New User Voice",
    component: NewUserVoice,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/user-voices/edit/:id",
    name: "Edit User Voice",
    component: EditUserVoice,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/categories/list",
    name: "Categories",
    component: Categories,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/categories/new",
    name: "New Category",
    component: NewCategory,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/categories/edit/:id",
    name: "Edit Category",
    component: EditCategory,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/items/list",
    name: "Items",
    component: Items,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/items/new",
    name: "New Item",
    component: NewItem,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/items/edit/:id",
    name: "Edit Item",
    component: EditItem,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/service-categories/list",
    name: "Service Categories",
    component: ServiceCategories,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/service-categories/new",
    name: "New Service Category",
    component: NewServiceCategory,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/service-categories/edit/:id",
    name: "Edit Service Category",
    component: EditServiceCategory,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/service-subcategories/list",
    name: "Service Subcategories",
    component: ServiceSubcategories,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/service-subcategories/new",
    name: "New Service Subcategory",
    component: NewServiceSubcategory,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/service-subcategories/edit/:id",
    name: "Edit Service Subcategory",
    component: EditServiceSubcategory,
    meta: {
      middleware: [auth, adminCreator],
    },
  },
  {
    path: "/service-providers/list",
    name: "Service Providers",
    component: ServiceProviders,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/service-providers/new",
    name: "New Service Provider",
    component: NewServiceProvider,
    meta: {
      middleware: [auth],
    },
  },
  {
    path: "/service-providers/edit/:id",
    name: "Edit Service Provider",
    component: EditServiceProvider,
    meta: {
      middleware: [auth],
    },
  },
];

const router = createRouter({
  history: createWebHistory(process.env.BASE_URL),
  routes,
  linkActiveClass: "active",
});


function nextFactory(context, middleware, index) {
  const subsequentMiddleware = middleware[index];
  if (!subsequentMiddleware) return context.next;

  return (...parameters) => {
    context.next(...parameters);
    const nextMiddleware = nextFactory(context, middleware, index + 1);
    subsequentMiddleware({ ...context, next: nextMiddleware });
  };
}


router.beforeEach((to, from, next) => {
  if (!to.meta.middleware) {
    to.meta.middleware = [auth];
  }

  const middleware = to.meta.middleware;

  const context = {
    from,
    next,
    router,
    to,
  };
  const nextMiddleware = nextFactory(context, middleware, 1);

  return middleware[0]({ ...context, next: nextMiddleware });
});

export default router;
