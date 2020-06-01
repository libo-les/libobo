<template>
  <div id="Sidebar">
    <div class="SidebarUl">
      <li class="leftNavLi" v-for="(item, index) in fullName" :key="index">
        <template v-if="!item.sub_menu">
          <dl
            class="leftNavDl bntActive"
            :class="[linkIndex === index ? 'leftNavLi_active' : '',]"
            @click="linkIndexFun(index,true)"
          >
            <router-link :to="`/${item.path}`">
              <span class="leftNavName noneTexr">{{ item.name }}</span>
            </router-link>
          </dl>
        </template>
        <template v-else>
          <dl
            class="leftNavDl bntActive"
            @click="linkIndexFun(index)"
            :class="{ 'linkIndex2_active ': linkIndex2 === index && linkIndex !== linkIndex2}"
          >
            <span class="leftNavName noneTexr">{{ item.name }}</span>
          </dl>
          <dl
            class="leftNavLiDl noneTexr"
            :class="{ 'leftNavLiDl_active ': linkIndex !== index }"
          >
            <dd
              class="leftNavLiDd"
              v-for="(item1, index1) in item.sub_menu"
              :key="index1"
              @click="linkIndexFun2(index)"
            >
              <router-link :to="item1.path ? `/${item1.path}` : '/'">
                <span class="leftNavName2">{{ item1.name }}</span>
              </router-link>
            </dd>
          </dl>
        </template>
      </li>
    </div>
  </div>
</template>

<script>
import { mapState, mapMutations } from 'vuex';

export default {
  name: 'Sidebar',
  props: {
    pageType: String, // 总页数
  },
  data() {
    return {
      linkData: [],
      // linkDataPersonnel: [
      //   {
      //     path: '/Personnel/User',
      //     name: '用户管理',
      //     icon: 'home',
      //   },
      //   {
      //     name: '代言人管理',
      //     sub_menu: [
      //       {
      //         path: '/Personnel/Spokesman',
      //         name: '代言人列表', // 2218.51
      //       },
      //       {
      //         path: '/Personnel/SpokesmanCondition',
      //         name: '成为代言人条件',
      //       },
      //       // {
      //       //   path: '/Personnel/SpokesmanSetUp',
      //       //   name: '代言人设置',
      //       // },
      //     ],
      //   },
      //   {
      //     name: '代理商管理',
      //     sub_menu: [
      //       {
      //         path: '/Personnel/Agent',
      //         name: '代理商列表',
      //       },
      //       {
      //         path: '/Personnel/AgentExamine',
      //         name: '代理商审核',
      //       },
      //       {
      //         path: '/Personnel/AgentLevel',
      //         name: '代理商等级',
      //       },
      //       {
      //         path: '/Personnel/AgentLevelSetUp',
      //         name: '代理商设置',
      //       },
      //     ],
      //   },
      // ],
      // linkDataManage: [
      //   {
      //     path: '/Manage/Home',
      //     name: '首页',
      //   },
      //   {
      //     path: '/Manage/ShopSet',
      //     name: '店铺设置',
      //   },
      //   {
      //     name: '商品管理',
      //     sub_menu: [
      //       {
      //         path: '/Manage/GoodsManage',
      //         name: '商品列表',
      //       },
      //       {
      //         path: '/Manage/GoodsCategory',
      //         name: '商品分类',
      //       },
      //       {
      //         path: '/Manage/GoodsBrand',
      //         name: '商品品牌',
      //       },
      //     ],
      //   },
      //   {
      //     path: '/Manage/Order',
      //     name: '订单中心',
      //   },
      //   {
      //     name: '任务管理',
      //     // path: '/Manage/Task',
      //     sub_menu: [
      //       {
      //         path: '/Manage/Task',
      //         name: '分享任务',
      //       },
      //       {
      //         path: '/Manage/SpecialTask',
      //         name: '特殊任务',
      //       },
      //       {
      //         path: '/Manage/TaskExamine',
      //         name: '特殊任务审核',
      //       },
      //     ],
      //   },
      //   {
      //     name: '广告管理',
      //     // path: '/Manage/Advertisement',
      //     sub_menu: [
      //       {
      //         path: '/Manage/Advertisement',
      //         name: '广告列表',
      //       },
      //       {
      //         path: '/Manage/Position',
      //         name: '广告位置',
      //       },
      //       {
      //         path: '/Manage/AdvertisementLink',
      //         name: '广告链接',
      //       },
      //     ],
      //   },
      //   // {
      //   //   path: '/Manage/Turnover',
      //   //   name: '商家流水',
      //   // },
      //   {
      //     path: '/Manage/CashRecord',
      //     name: '提现记录',
      //   },
      // ],
      // linkDataSetUp: [
      //   {
      //     name: '权限',
      //     sub_menu: [
      //       {
      //         path: '/SetUp/Limits',
      //         name: '管理员列表',
      //       },
      //       {
      //         path: '/SetUp/RoleManagement',
      //         name: '角色管理',
      //       },
      //       {
      //         path: '/SetUp/RolePermissions',
      //         name: '模块列表',
      //       },
      //     ],
      //   },
      //   // {
      //   //   path: '/SetUp/Delivery',
      //   //   name: '配送设置',
      //   // },
      //   {
      //     name: '系统设置',
      //     sub_menu: [
      //       // {
      //       //   path: '/SetUp/AdministratorLog',
      //       //   name: '管理员日志',
      //       // },
      //       // {
      //       //   path: '/SetUp/PushMessage',
      //       //   name: '推送消息',
      //       // },
      //       {
      //         path: '/SetUp/systemInformation',
      //         name: '系统信息',
      //       },
      //     ],
      //   },
      // ],
    };
  },
  computed: {
    // 使用对象展开运算符将此对象混入到外部对象中
    ...mapState({
      linkIndex: state => state.linkIndex,
      linkIndex2: state => state.linkIndex2,
      linkDataPersonnel: state => state.linkDataPersonnel,
      linkDataManage: state => state.linkDataManage,
      linkDataSetUp: state => state.linkDataSetUp,
    }),
    fullName() {
      if (this.pageType === 'Personnel') {
        return this.linkDataPersonnel;
      } if (this.pageType === 'Manage') {
        return this.linkDataManage;
      } if (this.pageType === 'SetUp') {
        return this.linkDataSetUp;
      }
      return false;
    },
  },
  methods: {
    ...mapMutations({
      linkIndexFunVuex: 'linkIndexFun',
      linkIndex2FunVuex: 'linkIndex2Fun',
    }),
    linkIndexFun(index, type) {
      if (this.linkIndex === index) {
        this.linkIndexFunVuex(-1);
      } else {
        this.linkIndexFunVuex(index);
        localStorage.setItem('linkIndex', this.linkIndex);
      }
      if (type) {
        this.linkIndex2FunVuex(-1);
        localStorage.setItem('linkIndex2', this.linkIndex2);
      }
    },
    linkIndexFun2(index) {
      if (this.linkIndex2 !== index) {
        this.linkIndex2FunVuex(index);
        localStorage.setItem('linkIndex2', this.linkIndex2);
      }
    },
  },
  mounted() {
    setTimeout(() => {
      if (localStorage.getItem('linkIndex')) {
        this.linkIndexFunVuex(Number(localStorage.getItem('linkIndex')));
        this.linkIndex2FunVuex(Number(localStorage.getItem('linkIndex2')));
        // localStorage.removeItem('linkIndex');
        // localStorage.removeItem('linkIndex2');
      }
    }, 100);
  },
};
</script>

<style scoped>
#Sidebar{
  width: 280px;
  height: 100%;
  position: absolute;
  top: 0;
  left: 0;
  background: #323A4D;
}
.SidebarUl {
  padding-top: 20px;
}
.leftNavDl {
  width:280px;
  height:72px;
  color: #ffffff;
  line-height: 72px;
}
.leftNavDl a {
  display: block;
  color: #ffffff;
  width: 100%;
  height: 100%;
}
.leftNavName {
  padding-left: 40px;
}
.leftNavDl:hover {
  /*background: #2B3245;*/
}
.leftNavDl>.router-link-active{
  background: #2B3245;
  position: relative;
}
.leftNavDl>.router-link-active:before {
  content: ' ';
  display: block;
  height: 100%;
  width:3px;
  background:linear-gradient(0deg,rgba(255,150,0,1) 0%,rgba(253,84,87,1) 100%);
  border-radius:2px;
  position: absolute;
  right: 0;
}
.linkIndex2_active {
  position: relative;
}
.linkIndex2_active:before {
  content: ' ';
  display: block;
  height: 100%;
  width:3px;
  background:linear-gradient(0deg,rgba(255,150,0,1) 0%,rgba(253,84,87,1) 100%);
  border-radius:2px;
  position: absolute;
  right: 0;
}
/*.leftNavLi_active {*/
  /*background: #2B3245;*/
/*}*/
.leftNavLiDl_active {
  display: none;
}
/*.leftNavDl > .router-link-active {*/
  /*display: block;*/
  /*background: #000;*/
/*}*/
.leftNavLiDd {
  width:280px;
  height:72px;
  background:#2B3245;
  line-height: 72px;
}
.leftNavLiDd a{
  display: block;
  width: 100%;
  height: 100%;
  color: #ffffff;
  position: relative;
}
.leftNavLiDd>.router-link-active:before {
  content: ' ';
  display: block;
  height: 100%;
  width:3px;
  background:linear-gradient(0deg,rgba(255,150,0,1) 0%,rgba(253,84,87,1) 100%);
  border-radius:2px;
  position: absolute;
  right: 0;
}
.leftNavName2 {
  padding-left: 60px;
}
</style>
