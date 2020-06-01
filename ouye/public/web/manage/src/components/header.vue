<template>
  <div id="headerBox">
    <div class="headerBoxLeft">
      <div class="headerImg">
        <img src="../assets/image/logo.png" alt="">
      </div>
      <div class="headerTitle">
        代乐乐云仓储系统
      </div>
      <div class="headerTitle2">
        V1.0
      </div>
    </div>
    <div class="headerBoxCenter">
      <div class="headerBoxCenterButton" v-for="(e, i) in powerlist" :key="i">
        <div
          class="headerBoxCenterText noneTexr bntActive"
          @click="linkIndexFun(0, 1, e.path, e)">
          <router-link class="" :to="`/${e.path}`" style="pointer-events: none">
            {{e.name}}
          </router-link>
          <div class="headerButtonB bntActive"></div>
        </div>
      </div>
    </div>
    <div class="headerBoxRight">
      <el-dropdown placement="bottom-end" @command="handleCommand">
        <span class="el-dropdown-link">
          {{data.user_name}}<i class="el-icon-caret-bottom el-icon--right"></i>
        </span>
        <el-dropdown-menu slot="dropdown">
          <el-dropdown-item command="ModifyPwd" class="inforCenter">
            修改密码
          </el-dropdown-item>
          <el-dropdown-item command="d" disabled class="inforCenter">
            最后登录：{{data.last_login_time | momentFun}}
          </el-dropdown-item>
          <el-dropdown-item
            command="SignOut" divided
            class="inforCenter">
            退出
          </el-dropdown-item>
        </el-dropdown-menu>
      </el-dropdown>
    </div>
  </div>
</template>

<script>
import { mapMutations } from 'vuex';
import moment from 'moment';

export default {
  name: 'headerBox',
  data() {
    return {
      data: {},
      powerlist: [],
      isLink: '',
    };
  },
  methods: {
    ...mapMutations({
      linkDataVuex: 'linkData',
    }),
    linkIndexFun(index, type, str, e) {
      localStorage.setItem('linkIndex', index);
      localStorage.setItem('linkIndex2', index);
      const link = this.dgLinkFun(e);
      if (this.$route.path !== `/${link}`) {
        this.isLink = str;
        if (type === 1) {
          // if (str === 'shop')
          // this.powerlist[shop].
          // console.log(e, link);
          this.$router.push(`/${link}`);
        }
      }
    },
    dgLinkFun(data) {
      if (data.sub_menu) {
        return this.dgLinkFun(data.sub_menu[0]);
      }
      return data.path;
    },
    handleCommand(command) {
      if (command === 'SignOut') {
        this.SignOut();
      } else if (command === 'ModifyPwd') {
        this.$router.push(`/SetUp/Limits/Password?CateId=${this.data.id}&userName=${this.data.user_name}`);
      }
    },
    SignOut() {
      this.$axios({
        method: 'get',
        responseType: 'json',
        url: '/admin/login/logOut',
      }).then((res) => {
        if (res.data.errcode === 0) {
          window.localStorage.removeItem('token');
          this.$router.push('/login');
        } else {
          this.$message.error(res.data.errmsg);
        }
      }).catch(() => {
        this.loading2.close();
        this.$message({
          type: 'error',
          message: '请求失败',
        });
      });
    },
    postFun() {
      this.$axios({
        method: 'get',
        responseType: 'json',
        url: '/admin/login/headnews',
      }).then((res) => {
        if (res.data.errcode === 0) {
          this.data = res.data.result.massage;
          this.powerlist = res.data.result.powerlist;
          this.linkDataVuex({
            name: 'host',
            value: res.data.result.sevre
            ,
          });
          this.powerlist.forEach((e) => {
            if (e.path === 'Personnel') {
              this.linkDataVuex({
                name: 'linkDataPersonnel',
                value: e.sub_menu,
              });
            } else if (e.path === 'Manage') {
              this.linkDataVuex({
                name: 'linkDataManage',
                value: e.sub_menu,
              });
            } else if (e.path === 'SetUp') {
              this.linkDataVuex({
                name: 'linkDataSetUp',
                value: e.sub_menu,
              });
            }
          });
        } else {
          this.$message.error(res.data.errmsg);
        }
      }).catch(() => {
        this.$message({
          type: 'error',
          message: '请求失败',
        });
      });
    },
  },
  mounted() {
    this.postFun();
  },
  filters: {
    momentFun(value) {
      return moment(value * 1000).format('YYYY-MM-DD');
    },
  },
};
</script>

<style scoped>
#headerBox {
  width: 100%;
  height: 72px;
  position: absolute;
  top: 0;
  left: 0;
  background: #2B3245;
}
.headerBoxLeft {
  height: 100%;
  float: left;
  padding: 20px 30px;
}
.headerBoxRight {
  height: 100%;
  float: right;
  padding-right: 40px;
}
.el-dropdown-link {
  display: block;
  font-size:18px;
  font-weight: 400;
  color: rgba(250,251,253,1);
  line-height: 72px;
}
.headerBoxCenter {
  position: absolute;
  width: 560px;
  height: 100%;
  top: 0;
  left: 0;
  right: 0;
  margin: 0 auto;
  display: flex;
  display: -webkit-flex;
  display: -moz-flex;
  display: -ms-flexbox;
  -webkit-justify-content:center;
  justify-content:center;
  -moz-box-pack:center;
  -webkit--moz-box-pack:center;
  box-pack:center;
}
.headerBoxCenterButton {
  width: 140px;
  height: 100%;
  float: left;
  padding: 0 10px;
}
.headerBoxCenterText {
  width: 100%;
  height: 100%;
  position: relative;
}
.headerBoxCenterText:hover {
  background: #1e222f
}
.headerBoxCenterText a {
  display: block;
  height: 100%;
  width: 100%;
  font-size:20px;
  font-weight:400;
  color:rgba(250,251,253,1);
  line-height: 72px;
  text-align: center;
  background: rgba(0,0,0,0);
  position: absolute;
  z-index: 2;
}
.headerButtonB {
  height: 100%;
  width: 100%;
  position: absolute;
  top: 0;
  left: 0;
  background:linear-gradient(45deg,rgba(255,150,0,1),rgba(253,84,87,1));
  z-index: 1;
  opacity: 0;
}
.router-link-active+.headerButtonB {
  opacity: 1;
}
.headerImg {
  width:32px;
  height:32px;
  float: left;
}
.headerImg img{
  display: block;
  width: 100%;
}
.headerTitle {
  float: left;
  margin-left: 10px;
  font-size:24px;
  font-weight:400;
  color:rgba(250,251,253,1);
  line-height:32px;
}
.headerTitle2 {
  height:24px;
  border:1px solid rgba(255,255,255,1);
  border-radius:4px;
  padding: 0 6px;
  margin: 4px 8px;
  float: left;
  font-size:18px;
  font-weight:400;
  color:rgba(250,251,253,1);
  line-height:22px;
}
</style>
