<template>
  <div id="cropper">
    <transition name="fade1">
      <div class="Mask" v-if="post.isShow"></div>
    </transition>
    <transition name="fade" mode="in-out">
      <div class="Mask1" v-show="post.isShow">
        <div class="CropperBox">
          <div class="CropperBoxHeader">
            <div class="maincontentIcon"></div>
            <div class="CropperBoxHeaderTitle">上传图片</div>
            <i class="el-icon-close CropperBoxClose" @click="CropperBoxClose"></i>
          </div>
          <div class="container">
            <div class="boxAfter">
              <div class="CropperViewBox">
                <VueCropper
                  class="CropperView"
                  ref="cropper"
                  :img="post.url"
                  :autoCrop="true"
                  :infoTrue="true"
                  :ssr="false"
                  :fixed="post.fixed"
                  :fixedNumber="[post.x, post.y]"
                  :centerBox="true"
                  :canMove="false"
                  @realTime="realTime"
                  @cropMoving="cropMoving"
                ></VueCropper>
              </div>
              <div class="crippertBottomBox">
              <div class="BottomBoxImg">
                <div :style="previewStyle1">
                  <div :style="previews.div">
                    <img :src="previews.url" :style="previews.img" />
                  </div>
                </div>
              </div>
              <div class="crippertBottomBoxTisp">裁切预览</div>
            </div>
            </div>
            <div class="CropperButtonBox">
              <div class="CropperButton1 noneTexr bntActive">
                <label for="inputImage" class="CropperLabel ">
                  <input
                    ref="imginput"
                    id="inputImage"
                    name="file"
                    class="sr-only"
                    type="file"
                    accept="image/*"
                    style="display: none;"
                    @change="uploadImg($event, 1)"
                  />
                  重新上传
                  <i class="el-icon-upload2"></i>
                </label>
              </div>
              <div class="CropperButton2 noneTexr bntActive" @click="$refs.cropper.rotateLeft(45)">
                <i class="el-icon-refresh-left"></i>
                旋转
              </div>
              <div
                class="CropperButton2 noneTexr bntActive"
                @click="$refs.cropper.changeScale(-1)">
                <i class="el-icon-zoom-out"></i>
                缩小
              </div>
              <div class="CropperButton2 noneTexr bntActive" @click="$refs.cropper.changeScale(1)">
                <i class="el-icon-zoom-in"></i>
                放大
              </div>
            </div>
          </div>
          <div class="CropperBoxFoot">
            <div class="CropperBoxButton noneTexr bntActive" @click="CropperBoxClose()">取消</div>
            <div class="CropperBoxButton noneTexr bntActive" @click="startCrop()">确定</div>
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>

<script>
import { VueCropper } from 'vue-cropper';

export default {
  name: 'cropper',
  props: {
    post: Object,
  },
  data() {
    return {
      param: {
        originalFilename: '',
        contentType: 'image/jpeg',
        base64: '', // 我们接口要求不要data:image/png;base64
      },
      previews: {
        img: {},
      },
      getCrop: {
        x: 0,
        y: 0,
      },
      flag: true,
      previewStyle1: {},
    };
  },
  components: {
    VueCropper,
  },
  methods: {
    CropperBoxClose() {
      this.post.isShow = 0;
    },
    // 上传图片（点击上传按钮）
    uploadImg(e) {
      const than = this;
      // 上传图片
      const file = e.target.files[0];
      if (!/\.(gif|jpg|jpeg|png|bmp|GIF|JPG|PNG)$/.test(e.target.value)) {
        alert('图片类型必须是.gif,jpeg,jpg,png,bmp中的一种');
        return;
      }
      this.param.originalFilename = file.name;
      const reader = new FileReader();
      reader.readAsDataURL(file);
      reader.onloadend = function () {
        than.post.url = this.result;
      };
      // 转化为blob
      // reader.readAsArrayBuffer(file);
    },
    // 实时预览函数
    realTime(data) {
      const data1 = {};
      data1.h = Math.round(data.h);
      data1.w = Math.round(data.w);
      this.previews = data;
      this.previewStyle1 = {
        width: `${data1.w}px`,
        height: `${data1.h}px`,
        overflow: 'hidden',
        margin: '0',
        zoom:
          data.w * (223 / data.h) > 223
            ? 223 / data.w
            : (data.h * (223 / data.w) > 223
              ? 223 / data.h
              : data.w >= 223 && data.h <= 223
                ? 223 / data.w
                : 223 / data.h),
      };
    },
    cropMoving() {
      const getCropAxis = this.$refs.cropper.getCropAxis();
      const getImgAxis = this.$refs.cropper.getImgAxis();
      this.getCrop.x = Math.round(getCropAxis.x1 - getImgAxis.x1);
      this.getCrop.y = Math.round(getCropAxis.y1 - getImgAxis.y1);
    },
    startCrop() {
      if (!this.flag) return;
      this.flag = false;
      this.$refs.cropper.getCropData((data) => {
        // do something
        this.cropperSrc = data;
        // console.log(data.replace("data:image/png;base64,", ""));
        this.param.base64 = data.replace('data:image/png;base64,', '');
        this.$emit('startCropFun', this.param);
        this.flag = true;
      });
    },
  },
  watch: {
    'post.isShow': function (val) {
      if (val && !this.post.url) {
        this.$refs.imginput.click();
      }
    },
  },
};
</script>

<style scoped>
  #cropper {
    width: 0;
    height: 0;
  }
  .Mask {
    width: 100%;
    height: 100%;
    display: block;
    position: fixed;
    top: 0;
    left: 0;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
  }
  .Mask1 {
    width: 100%;
    height: 100%;
    position: fixed;
    top: 0;
    left: 0;
    background: rgba(0, 0, 0, 0);
    display: -webkit-box;
    display: -webkit-flex;
    display: -moz-box;
    display: -ms-flexbox;
    display: flex;
    -webkit-box-pack: center;
    -webkit-justify-content: center;
    -moz-box-pack: center;
    -ms-flex-pack: center;
    justify-content: center;
    -webkit-box-align: center;
    -webkit-align-items: center;
    -moz-box-align: center;
    -ms-flex-align: center;
    align-items: center;
    z-index: 1000;
    overflow: auto;
  }
  .CropperBox {
    width:1070px;
    height:757px;
    background: #ffffff;
    position: relative;
  }
  .CropperBoxHeader {
    height:56px;
    width: 100%;
    background:rgba(249,249,249,1);
    position: relative;
  }
  .maincontentIcon {
    left: 20px;
  }
  .boxAfter:after {
    content: ' ';
    height: 0;
    display: block;
    visibility: hidden;
    clear: both;
  }
  .CropperBoxHeaderTitle {
    padding-left: 40px;
    font-size:16px;
    font-weight:400;
    color:rgba(51,51,51,1);
    line-height: 56px;
  }
  .CropperBoxFoot {
    height:56px;
    width: 100%;
    background:rgba(249,249,249,1);
    position: absolute;
    bottom: 0;
    padding: 12px;
  }
  .CropperBoxTitle {
    height: 30px;
    font-size: 30px;
    font-weight: bold;
    color: rgba(0, 0, 0, 1);
    line-height: 30px;
    text-align: left;
  }
  .CropperBoxClose {
    width: 16px;
    height: 16px;
    cursor: pointer;
    position: absolute;
    top: 0;
    right: 25px;
    bottom: 0;
    margin: auto;
    font-size: 22px;
  }
  .container {
    width: 100%;
    padding: 30px;
  }
  .container:after {
    content: "";
    display: block;
    height: 0;
    visibility: hidden;
    clear: both;
  }
  .CropperButtonBox {
    width: 767px;
    margin-top: 20px;
  }
  .CropperButtonBox:after {
    content: "";
    height: 0;
    display: block;
    visibility: hidden;
    clear: both;
  }
  .CropperButton {
    display: block;
    height: 32px;
    width: 38px;
    float: left;
    background: #0088FE;
    line-height: 32px;
    text-align: center;
    border: 1px #0067eb solid;
    border-right: none;
    color: #ffffff;
  }
  .CropperButton:last-child {
    border-right: 1px #0067eb solid;
    border-radius: 0 4px 4px 0;
  }
  .CropperButton:first-child {
    border-radius: 4px 0 0 4px;
  }
  .CropperButton:hover {
    cursor: pointer;
    background: #0067eb;
  }
  .CropperButton1 {
    float: left;
    font-size:18px;
    font-weight:400;
    color: #0088FE;
    line-height: 18px;
  }
  .CropperButton1 label:hover {
    cursor: pointer;
  }
  .CropperButton2 {
    float: right;
    font-size:18px;
    font-weight:400;
    color: #0088FE;
    line-height: 18px;
    margin-left: 20px;
  }
  .CropperButton2:hover {
    cursor: pointer;
  }
  .CropperViewBox {
    float: left;
    width:767px;
    height:529px;
  }
  .CropperLabel {
    width: 100%;
    float: left;
    text-align: center;
  }
  .CropperLabel:after {
    content: "";
    height: 0;
    display: block;
    visibility: hidden;
    clear: both;
  }
  .CropperView {
    width:767px;
    height:529px;
  }
  /*下方预览图 和样式*/
  .crippertBottomBox {
    /*width: 100%;*/
    /*margin-top: 20px;*/
    float: right;
    width: calc( 100% - 787px);
    height: 223px;
    position: relative;
  }
  .crippertBottomBox:after {
    content: "";
    height: 0;
    display: block;
    visibility: hidden;
    clear: both;
  }
  .crippertBottomBoxTisp {
    position: absolute;
    width: 100%;
    bottom: -30px;
    font-size:14px;
    font-weight:400;
    color:rgba(51,51,51,1);
    line-height: 14px;
    text-align: center;
  }
  .BottomBoxImg {
    margin: auto;
    /*width: 148px;*/
    /*height: 148px;*/
    /*background: url("../assets/image/background.png") 0 0 / 1200px;*/
    overflow: hidden;
  }
  .BottomBoxImg img {
    display: block;
  }
  .BottomBoxImg>div {
    margin: 0 auto!important;
  }
  .BottomBoxButton {
    width: 200px;
    float: right;
  }
  .BottomBoxButton:after {
    content: "";
    height: 0;
    display: block;
    visibility: hidden;
    clear: both;
  }
  .input-group {
    margin-bottom: 10px;
    position: relative;
    display: table;
    border-collapse: separate;
  }
  .input-group-addon {
    display: block;
    float: left;
    width: 80px;
    padding: 6px 12px;
    font-size: 14px;
    font-weight: 400;
    line-height: 20px;
    color: #555;
    text-align: center;
    background-color: #eee;
    border: 1px solid #ccc;
    border-right: 0;
    border-top-left-radius: 4px;
    border-bottom-left-radius: 4px;
  }
  .BottomBoxButton_data {
    width: 70px;
    position: relative;
    float: left;
    z-index: 2;
    margin-bottom: 0;
    display: block;
    height: 34px;
    padding: 6px 12px;
    font-size: 14px;
    color: #555;
    background-color: #fff;
    background-image: none;
    border: 1px solid #ccc;
    overflow: hidden;
  }
  .input-group-addon2 {
    width: 50px;
    border-radius: 0 4px 4px 0;
    border: 1px solid #ccc;
    border-left: 0;
  }
  .CropperBoxButton {
    width:96px;
    height:30px;
    background: #0088FE;
    -webkit-border-radius: 4px;
    -moz-border-radius: 4px;
    border-radius: 4px;
    line-height: 30px;
    text-align: center;
    font-size: 14px;
    color: #ffffff;
    font-weight: 400;
    float: right;
    margin-left: 20px;
  }
  .CropperBoxButton:hover {
    cursor: pointer;
    background: #0067eb;
  }
  .fade-enter-active {
    animation: bouncein 0.5s;
  }
  .fade-leave-active {
    animation: bouncein 0.3s reverse;
  }
  .fade1-leave-active {
    transition-delay: 0.3s;
    /*animation: bouncein .5s reverse;*/
  }
  .fade2-enter-active {
    animation: bouncein 0.5s;
  }
  .fade2-leave-active {
    animation: bouncein 0.3s reverse;
  }
  @-webkit-keyframes bouncein {
    0% {
      opacity: 0;
      -webkit-transform: scale(0.3);
    }
    100% {
      opacity: 1;
      -webkit-transform: scale(1);
    }
  }
  @-moz-keyframes bouncein {
    0% {
      opacity: 0;
      -moz-transform: scale(0.3);
    }
    100% {
      opacity: 1;
      -webkit-transform: scale(1);
    }
  }
  @-ms-keyframes bouncein {
    0% {
      opacity: 0;
      -ms-transform: scale(0.3);
    }
    100% {
      opacity: 1;
      -webkit-transform: scale(1);
    }
  }
  @keyframes bouncein {
    0% {
      opacity: 0;
      transform: scale(0.3);
    }
    100% {
      opacity: 1;
      -webkit-transform: scale(1);
    }
  }
</style>
