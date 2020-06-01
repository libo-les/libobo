import Vue from 'vue';
import Router from 'vue-router';
import Home from './views/Home.vue';

Vue.use(Router);

export default new Router({
  // mode: 'history',
  base: process.env.BASE_URL,
  routes: [
    {
      path: '/',
      component: Home,
      meta: { login: true },
      children: [
        {
          path: '',
          name: 'home', // 首页目前跳转到商品管理页
          redirect: '/Manage',
          meta: { login: true },
        },
        {
          path: '/Manage',
          name: 'Manage', // 店铺管理
          redirect: '/Manage/Home',
          meta: { login: true },
          component: () => import('./views/Manage/Manage.vue'),
          children: [
            {
              path: '/Manage/Home', // 首页
              name: 'Home',
              meta: { login: true },
              component: () => import('./views/Manage/Home/Home.vue'),
            },
            {
              path: '/Manage/ShopSet', // 店铺设置
              name: 'ShopSet',
              meta: { login: true },
              component: () => import('./views/Manage/ShopSet/ShopSet.vue'),
            },
            {
              path: '/Manage/GoodsManage',
              name: 'GoodsManage', // 商品管理
              meta: { login: true },
              component: () => import('./views/Manage/GoodsManage/GoodsManage.vue'),
            },
            {
              path: '/Manage/GoodsManage/GoodsManageAddEdit',
              name: 'GoodsManageAddEdit', // 商品添加
              meta: { login: true },
              component: () => import('./views/Manage/GoodsManage/GoodsManageAddEdit.vue'),
            },
            {
              path: '/Manage/GoodsManage/GoodsManageAudit',
              name: 'GoodsManageAddEdit', // 商品添加
              meta: { login: true },
              component: () => import('./views/Manage/GoodsManage/GoodsManageAudit.vue'),
            },
            {
              path: '/Manage/GoodsCategory',
              name: 'GoodsCategory', // 商品管理
              meta: { login: true },
              component: () => import('./views/Manage/GoodsManage/GoodsCategory.vue'),
            },
            {
              path: '/Manage/GoodsCategory/GoodsCategoryAddEdit',
              name: 'GoodsCategoryAddEdit', // 商品管理
              meta: { login: true },
              component: () => import('./views/Manage/GoodsManage/GoodsCategoryAddEdit.vue'),
            },
            {
              path: '/Manage/GoodsBrand',
              name: 'GoodsBrand', // 商品品牌
              meta: { login: true },
              component: () => import('./views/Manage/GoodsManage/GoodsBrand.vue'),
            },
            {
              path: '/Manage/GoodsBrand/GoodsBrandAddEdit',
              name: 'GoodsBrandAddEdit', // 商品品牌
              meta: { login: true },
              component: () => import('./views/Manage/GoodsManage/GoodsBrandAddEdit.vue'),
            },
            {
              path: '/Manage/Task',
              name: 'Task', // 任务列表
              meta: { login: true },
              component: () => import('./views/Manage/Task/Task.vue'),
            },
            {
              path: '/Manage/Task/TaskAddEdit',
              name: 'TaskAddEdit', // 任务添加
              meta: { login: true },
              component: () => import('./views/Manage/Task/TaskAddEdit.vue'),
            },
            {
              path: '/Manage/Task/TaskAudit',
              name: 'TaskAudit', // 任务详情
              meta: { login: true },
              component: () => import('./views/Manage/Task/TaskAudit.vue'),
            },
            {
              path: '/Manage/SpecialTask',
              name: 'SpecialTask', // 特殊任务
              meta: { login: true },
              component: () => import('./views/Manage/Task/SpecialTask.vue'),
            },
            {
              path: '/Manage/SpecialTask/SpecialTaskAddEdit',
              name: 'SpecialTaskAddEdit', // 特殊任务
              meta: { login: true },
              component: () => import('./views/Manage/Task/SpecialTaskAddEdit.vue'),
            },
            {
              path: '/Manage/SpecialTask/SpecialTaskAudit',
              name: 'SpecialTaskAudit', // 特殊任务
              meta: { login: true },
              component: () => import('./views/Manage/Task/SpecialTaskAudit.vue'),
            },
            {
              path: '/Manage/TaskExamine',
              name: 'TaskExamine', // 任务审核
              meta: { login: true },
              component: () => import('./views/Manage/Task/TaskExamine.vue'),
            },
            {
              path: '/Manage/TaskExamine/TaskExamineAudit',
              name: 'TaskExamineAudit', // 任务审核详情
              meta: { login: true },
              component: () => import('./views/Manage/Task/TaskExamineAudit.vue'),
            },
            {
              path: '/Manage/Advertisement',
              name: 'Advertisement', // 广告列表
              meta: { login: true },
              component: () => import('./views/Manage/Advertisement/Advertisement.vue'),
            },
            {
              path: '/Manage/Advertisement/AdvertisementAddEdit',
              name: 'Advertisement', // 广告添加
              meta: { login: true },
              component: () => import('./views/Manage/Advertisement/AdvertisementAddEdit.vue'),
            },
            {
              path: '/Manage/Position',
              name: 'Position', // 广告添加
              meta: { login: true },
              component: () => import('./views/Manage/Advertisement/Position.vue'),
            },
            {
              path: '/Manage/Position/PositionAddEdit',
              name: 'PositionAddEdit', // 广告添加
              meta: { login: true },
              component: () => import('./views/Manage/Advertisement/PositionAddEdit.vue'),
            },
            {
              path: '/Manage/AdvertisementLink',
              name: 'AdvertisementLink', // 广告链接
              meta: { login: true },
              component: () => import('./views/Manage/Advertisement/AdvertisementLink.vue'),
            },
            {
              path: '/Manage/AdvertisementLink/AdvertisementLinkAddEdit',
              name: 'AdvertisementLinkAddEdit', // 广告链接
              meta: { login: true },
              component: () => import('./views/Manage/Advertisement/AdvertisementLinkAddEdit.vue'),
            },
            {
              path: '/Manage/Order',
              name: 'Order', // 订单列表
              meta: { login: true },
              component: () => import('./views/Manage/Order/Order.vue'),
            },
            {
              path: '/Manage/Order/OrderDetails',
              name: 'OrderDetails', // 订单列表
              meta: { login: true },
              component: () => import('./views/Manage/Order/OrderDetails.vue'),
            },
            {
              path: '/Manage/Storage',
              name: 'Storage', // 订单列表
              meta: { login: true },
              component: () => import('./views/Manage/Storage/Storage.vue'),
            },
            {
              path: '/Manage/Storage/StorageDetails',
              name: 'StorageDetails', // 订单列表
              meta: { login: true },
              component: () => import('./views/Manage/Storage/StorageDetails.vue'),
            },
            // {
            //   path: '/Manage/Turnover',
            //   name: 'Turnover',
            //   meta: { login: true },
            //   component: () => import('./views/Manage/Turnover/Turnover.vue'),
            // },
            // {
            //   path: '/Manage/Turnover/TurnoverDetails',
            //   name: 'TurnoverDetails',
            //   meta: { login: true },
            //   component: () => import('./views/Manage/Turnover/TurnoverDetails.vue'),
            // },
            {
              path: '/Manage/CashRecord',
              name: 'CashRecord',
              meta: { login: true },
              component: () => import('./views/Manage/CashRecord/CashRecord.vue'),
            },
          ],
        },
        {
          path: '/SetUp',
          meta: { login: true },
          redirect: '/SetUp/Limits',
          component: () => import(/* webpackChunkName: "about" */ './views/SetUp/SetUp.vue'),
          children: [
            {
              path: '/SetUp/Limits',
              name: 'Limits',
              meta: { login: true },
              component: () => import('./views/SetUp/Limits/Limits.vue'),
            },
            {
              path: '/SetUp/Limits/LimitsAddEdit',
              name: 'LimitsAddEdit',
              meta: { login: true },
              component: () => import('./views/SetUp/Limits/LimitsAddEdit.vue'),
            },
            {
              path: '/SetUp/Limits/Password',
              name: 'LimitsPassword',
              meta: { login: true },
              component: () => import('./views/SetUp/Limits/Password.vue'),
            },
            {
              path: '/SetUp/RoleManagement',
              name: 'RoleManagement',
              meta: { login: true },
              component: () => import('./views/SetUp/RoleManagement/RoleManagement.vue'),
            },
            {
              path: '/SetUp/RoleManagement/RoleManagementAddEdit',
              name: 'RoleManagementAddEdit',
              meta: { login: true },
              component: () => import('./views/SetUp/RoleManagement/RoleManagementAddEdit.vue'),
            },
            {
              path: '/SetUp/RolePermissions',
              name: 'RolePermissions',
              meta: { login: true },
              component: () => import('./views/SetUp/RolePermissions/RolePermissions.vue'),
            },
            {
              path: '/SetUp/RolePermissions/RolePermissionsAddEdit',
              name: 'RolePermissionsAddEdit',
              meta: { login: true },
              component: () => import('./views/SetUp/RolePermissions/RolePermissionsAddEdit.vue'),
            },
            {
              path: '/SetUp/AdministratorLog',
              name: 'AdministratorLog',
              meta: { login: true },
              component: () => import('./views/SetUp/AdministratorLog/AdministratorLog.vue'),
            },
            {
              path: '/SetUp/PushMessage',
              name: 'PushMessage',
              meta: { login: true },
              component: () => import('./views/SetUp/Settings/PushMessage.vue'),
            },
            {
              path: '/SetUp/systemInformation',
              name: 'systemInformation',
              meta: { login: true },
              component: () => import('./views/SetUp/Settings/systemInformation.vue'),
            },
            {
              path: '/SetUp/Delivery',
              name: 'Delivery',
              meta: { login: true },
              component: () => import('./views/SetUp/Delivery/Delivery.vue'),
            },
          ],
        },
        {
          path: '/Personnel',
          name: 'Personnel',
          redirect: '/Personnel/User',
          meta: { login: true },
          component: () => import('./views/Personnel/Personnel.vue'),
          children: [
            {
              path: '',
              name: 'Personnel',
              redirect: '/Personnel/User',
              meta: { login: true },
              component: () => import('./views/Personnel/User/User.vue'),
            },
            {
              path: '/Personnel/User',
              name: 'PersonnelUser',
              meta: { login: true },
              component: () => import('./views/Personnel/User/User.vue'),
            },
            {
              path: '/Personnel/Spokesman',
              name: 'Spokesman',
              meta: { login: true },
              component: () => import('./views/Personnel/Spokesman/Spokesman.vue'),
            },
            {
              path: '/Personnel/SpokesmanExamine',
              name: 'SpokesmanExamine',
              meta: { login: true },
              component: () => import('./views/Personnel/Spokesman/SpokesmanExamine.vue'),
            },
            {
              path: '/Personnel/SpokesmanCondition',
              name: 'SpokesmanCondition',
              meta: { login: true },
              component: () => import('./views/Personnel/Spokesman/SpokesmanCondition.vue'),
            },
            // {
            //   path: '/Personnel/SpokesmanSetUp',
            //   name: 'SpokesmanSetUp',
            //   meta: { login: true },
            //   component: () => import('./views/Personnel/Spokesman/SpokesmanSetUp.vue'),
            // },
            {
              path: '/Personnel/Agent',
              name: 'Agent',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/Agent.vue'),
            },
            {
              path: '/Personnel/AgentExamine',
              name: 'AgentExamine',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentExamine.vue'),
            },
            {
              path: '/Personnel/AgentExamine/AgentExamineAudit',
              name: 'AgentExamineAudit',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentExamineAudit.vue'),
            },
            {
              path: '/Personnel/AgentLevel',
              name: 'AgentLevel',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentLevel.vue'),
            },
            {
              path: '/Personnel/AgentLevel/AgentLevelAddEdit',
              name: 'AgentLevelAddEdit',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentLevelAddEdit.vue'),
            },
            {
              path: '/Personnel/AgentLevelSetUp',
              name: 'AgentLevelSetUp',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentLevelSetUp.vue'),
            },
            {
              path: '/Personnel/AgentTask',
              name: 'AgentTask',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentTask.vue'),
            },
            {
              path: '/Personnel/AgentTask/AgentTaskAddEdit',
              name: 'AgentTaskAddEdit',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentTaskAddEdit.vue'),
            },
            {
              path: '/Personnel/AgentTask/AgentTaskAudit',
              name: 'AgentTaskAudit',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentTaskAudit.vue'),
            },
            {
              path: '/Personnel/AgentSpecialTask',
              name: 'AgentSpecialTask',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentSpecialTask.vue'),
            },
            {
              path: '/Personnel/AgentSpecialTask/AgentSpecialTaskAddEdit',
              name: 'AgentSpecialTaskAddEdit',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentSpecialTaskAddEdit.vue'),
            },
            {
              path: '/Personnel/AgentSpecialTask/AgentSpecialTaskAudit',
              name: 'AgentSpecialTaskAudit',
              meta: { login: true },
              component: () => import('./views/Personnel/Agent/AgentSpecialTaskAudit.vue'),
            },
          ],
        },
      ],
    },
    {
      path: '/login',
      name: 'login',
      meta: {
        login: false,
        type: 'login',
      },
      component: () => import(/* webpackChunkName: "about" */ './views/login/login.vue'),
    },
  ],
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    }
    return { x: 0, y: 0 };
  },
});
