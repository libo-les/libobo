<template>
  <div id="app">
    <router-view/>
  </div>
</template>

<style>
* {
  outline: none;
  margin: 0;
  padding: 0;
  text-decoration: none;
  list-style-type: none;
  border: none;
  box-sizing: border-box;
}
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  /*text-align: center;*/
  color: #2c3e50;
}
#nav a {
  font-weight: bold;
  color: #2c3e50;
}
#nav a.router-link-exact-active {
  color: #42b983;
}
.noneTexr {
  -webkit-touch-callout: none;
  -webkit-user-select: none;
  -khtml-user-select: none;
  -moz-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.bntActive {
  -webkit-transition: all 0.3s;
  -moz-transition: all 0.3s;
  -ms-transition: all 0.3s;
  -o-transition: all 0.3s;
  transition: all 0.3s;
}
.borderActive {
  -webkit-transition: border-color .2s cubic-bezier(.645, .045, .355, 1);
  -moz-transition: border-color .2s cubic-bezier(.645, .045, .355, 1);
  -ms-transition: border-color .2s cubic-bezier(.645, .045, .355, 1);
  -o-transition: border-color .2s cubic-bezier(.645, .045, .355, 1);
  transition: border-color .2s cubic-bezier(.645, .045, .355, 1);
}
/*面包屑*/
.Breadcrumb {
  height:46px;
  border-radius:4px;
  background: #ffffff;
  width: 100%;
  line-height: 46px;
  position: relative;
  padding-left: 35px;
  font-size:14px;
  font-weight:400;
}
.BreadcrumbIcon {
  width:10px;
  height:12px;
  background: url("assets/image/Breadcrumb.png") 0 0/cover no-repeat;
  position: absolute;
  left: 20px;
  top: 0;
  bottom: 0;
  margin: auto;
}
.Breadcrumb a {
  font-size:14px;
  font-weight:400;
  color: #333333;
}
.Breadcrumb a:last-child {
  color: #FF9600;
  font-weight:bold;
}
.Breadcrumb span:last-child {
  color: #FF9600;
  font-weight:bold;
}
.maincontent {
  border-radius: 4px;
  background: #ffffff;
  padding: 0 20px 20px;
  margin-top: 20px;
}
.maincontentTop {
  height: 64px;
  width: 100%;
  position: relative;
  padding-left: 20px;
  border-bottom: 1px #D1D1D1 solid;
}
.maincontentIcon {
  position: absolute;
  top: 0;
  bottom: 0;
  left: 0;
  margin: auto;
  width:14px;
  height:14px;
  background: url("assets/image/maincontentTop.png");
}
.maincontentTitle {
  line-height: 64px;
  font-size:16px;
  font-weight: 400;
  color: #333333;
}
.maincontentTable {
  /*min-height: 648px;*/
}
.maincontentTable th{
  /*text-align: center;*/
  font-size:16px;
  font-weight:400;
  color:rgba(51,51,51,1);
}
.maincontentTable tbody tr{
  height: 60px;
}
.maincontentTable tbody tr td{
  height: 60px;
  padding: 0;
}
.el-table--border::after, .el-table--group::after, .el-table::before{
  background-color: #D1D1D1;
}
.el-table__fixed-right::before, .el-table__fixed::before {
  background-color: #D1D1D1;
}
.ContentFromC .el-date-editor.el-input{
  width:217px;
}
.tableImg img{
  display: block;
  width: 100%;
  height: 100%;
  object-fit: cover;
}
.RightDate input {
  border: none;
  border-bottom: 1px solid #ECF1F5;
  -webkit-border-radius: 0;
  -moz-border-radius: 0;
  border-radius: 0;
  height: 28px;
}
.RightDate .el-input__icon {
  line-height:28px;
}
.el-date-editor.el-input, .el-date-editor.el-input__inner {
  width: 180px;
}
input::-webkit-input-placeholder {
  color: rgba(51,51,51,0.5);
}
input::-moz-placeholder {
  color: rgba(51,51,51,0.5);
}
input:-moz-placeholder {
  color: rgba(51,51,51,0.5);
}
input:-ms-input-placeholder {
  color: rgba(51,51,51,0.5);
}
 .el-tooltip__popper.is-light{
  border: none!important;;
  box-shadow:0 2px 4px 0 rgba(255,150,0,0.5)!important;
  padding: 0;
  opacity: 1;
}
.el-tooltip__popper.is-light .popper__arrow {
  width: 10px;
  height: 10px;
  border-color: rgba(0,0,0,0) !important;
  box-shadow:0 2px 4px 0 rgba(255,150,0,0.5)!important;
  -webkit-transform: rotate(45deg);
  -moz-transform: rotate(45deg);
  -ms-transform: rotate(45deg);
  -o-transform: rotate(45deg);
  transform: rotate(45deg);
  z-index: -1;
}
.maincontentTipsIext {
  font-size:12px;
  color:rgba(51,51,51,1);
  line-height:16px;
  opacity:0.7;
  padding: 10px;
  z-index: 10;
  background: #ffffff;
  border-radius: 4px;
}
/*选择器大小开始*/
.ContentFromC input {
  height: 30px;
}
.ContentFromC .el-input__icon {
  line-height: 30px;
}
.ContentFromC .el-input-number {
  line-height: 28px;
}
.imgass_box .el-button {
  border: none;
}
imgass_box. .el-button:focus, .imgass_box .el-button:hover {
  color: #606266;
  border-color: #ffffff;
  background-color: #ffffff;
}
.ContentFromC .el-cascader{
  line-height: 30px;
}
</style>
