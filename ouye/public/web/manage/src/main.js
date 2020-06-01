import Vue from 'vue';
import ElementUI from 'element-ui';
import 'element-ui/lib/theme-chalk/index.css';
import VEditor from 'yimo-vue-editor';
import axios from 'axios';
import router from './router';
import store from './store';
import App from './App.vue';

Vue.use(ElementUI);
Vue.config.productionTip = false;
// 路由登录
router.beforeEach((to, from, next) => {
  const token = window.localStorage.getItem('token');
  if (to.matched.some(record => record.meta.login)) {
    // this route requires auth, check if logged in
    // if not, redirect to login page.
    // !auth.loggedIn()
    if (!token) {
      next({
        path: '/login',
        query: { redirect: to.fullPath },
      });
    } else {
      next();
    }
  } else if (to.matched[0].meta.type === 'login' && token) {
    next({ path: '/' });
  } else {
    next(); // make sure to always call next()!
  }
});

// 请求拦截
axios.defaults.withCredentials = true;
Vue.prototype.$axios = axios;
axios.defaults.headers.post['Content-Type'] = 'application/x-www-form-urlencoded; charset=UTF-8';
axios.defaults.headers.post['X-Requested-With'] = 'XMLHttpRequest';
axios.interceptors.response.use((response) => {
  // 对响应数据做点什么
  if (response.data.errcode === 10004) {
    // 没有登陆
    window.localStorage.removeItem('token');
    router.push('/login');
  } else if (response.data.errcode === 10005) {
    console.log(11111);
    // ElementUI.Message.info('没有权限');
    // router.go(-1);
    // window.location.href = '/ErrPage';
    window.history.go(-1);
    // window.history.back();
  } else {
    // let fromurl = document.referrer;
  }
  return response;
},
(error) => {
  // 对响应错误做点什么
  // ElementUI.Message.info('加载失败');
  return Promise.reject(error);
});
// 富文本编辑器
Vue.use(VEditor, {
  name: 'v-editor-app', // Custom name
  config: {
    uploadImgUrl: '/admin/base/Futext',
    uploadParams: {},
    menus: [
      'bold',
      'underline',
      'italic',
      'strikethrough',
      'eraser',
      'forecolor',
      'bgcolor',
      '|',
      'quote',
      'fontfamily',
      'fontsize',
      'head',
      'unorderlist',
      'orderlist',
      'alignleft',
      'aligncenter',
      'alignright',
      '|',
      'link',
      'unlink',
      'emotion',
      '|',
      'img',
      'location',
      'insertcode',
      '|',
      'undo',
      'redo',
      'fullscreen',
    ],
  }, // wagnEditor config
  uploadHandler: (type, resTxt) => {
    // Upload processing hook
    if (type === 'success') {
      const res = JSON.parse(resTxt);
      // Do not process the default look at the return value bit image path
      if (res.errcode !== 0) {
        return null;
      }
      return `/${res.result}`;
    } else if (type === 'error') {
      // todo toast
    } else if (type === 'timeout') {
      // todo toast
    }
    return 'upload failed__';
  },
});

new Vue({
  router,
  store,
  render: h => h(App),
}).$mount('#app');
