<template>
  <div id="paging">
    <div class="page_div" v-if="PageCount > 1">
      <div class="firstPage" @click="$emit('pageNone', 1)">
        <div class="firstPageBack"></div>
      </div>
      <div
        v-for="item in pageArr"
        :key="item"
        :class="[item === CurrentPage ? 'current' : '']"
        @click="$emit('pageNone',item)"
      >
        {{ item }}
      </div>
      <div class="nextPage" @click="$emit('pageNone', PageCount)">
        <div class="nextPageBack"></div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  name: 'Pageing',
  props: {
    PageCount: Number, // 总页数
    CurrentPage: Number, // 当前页数
  },
  data() {
    return {
      pageArr: [],
    };
  },
  methods: {
    pageFun(CurrentPage, PageCount) {
      const pageArr = [];
      for (let i = 1; i <= PageCount; i += 1) {
        if (CurrentPage <= 3) {
          if (i < 6) {
            pageArr.push(i);
          }
        } else if (CurrentPage > PageCount - 3) {
          if (i > PageCount - 5 && i <= PageCount) {
            pageArr.push(i);
          }
        } else if (i > CurrentPage - 3 && i < CurrentPage - 0 + 3) {
          pageArr.push(i);
        }
      }
      this.pageArr = pageArr;
    },
  },
  watch: {
    CurrentPage() {
      this.pageFun(this.CurrentPage, this.PageCount);
    },
    PageCount() {
      this.pageFun(this.CurrentPage, this.PageCount);
    },
  },
  mounted() {
    this.pageFun(this.CurrentPage, this.PageCount);
  },
};
</script>

<style scoped>
#paging:after, .page_div:after {
  content: '';
  display: block;
  height: 0;
  visibility: hidden;
  clear: both;
}
.page_div{
  font-size: 15px;
  color: #666666;
  margin-right: 10px;
  box-sizing: border-box;
  moz-user-select: -moz-none;
  -moz-user-select: none;
  -o-user-select: none;
  -khtml-user-select: none;
  -webkit-user-select: none;
  -ms-user-select: none;
  user-select: none;
}
.page_div>div{
  width:36px;
  height:36px;
  color: #333333;
  text-align: center;
  cursor: pointer;
  font-size: 16px;
  line-height: 36px;
  float: left;
  border:1px solid #D1D1D1;
  border-right:none;
}
.firstPage+div {
  border-left:none;
}
div.prePage,
div.nextPage {
  width:36px;
  height:36px;
  line-height: 36px;
  background: #ffffff;
  border:1px solid #D1D1D1;
  border-radius:0 4px 4px 0;
  position: relative;
}
.nextPage .nextPageBack{
  position: absolute;
  top: 0;
  right: 0;
  left: 0;
  bottom: 0;
  margin: auto;
  width:12px;
  height:12px;
  background: url("../assets/image/paging.png") 0 0 /cover no-repeat;
  -webkit-transform: rotate(180deg);
  -moz-transform: rotate(180deg);
  -ms-transform: rotate(180deg);
  -o-transform: rotate(180deg);
  transform: rotate(180deg);
}
.page_div .current {
  background: #0088FE;
  color: #FFFFFF;
}
div.firstPage {
  width:36px;
  height:36px;
  line-height: 36px;
  background: #ffffff;
  border:1px solid #D1D1D1;
  border-radius:4px 0 0 4px;
  position: relative;
}
.firstPage .firstPageBack{
  position: absolute;
  top: 0;
  right: 0;
  left: 0;
  bottom: 0;
  margin: auto;
  width:12px;
  height:12px;
  background: url("../assets/image/paging.png") 0 0 /cover no-repeat;
}
</style>
