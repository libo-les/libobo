<template>
  <div class="x-bar" style="width: 100%; height: 100%;">
    <div :id="id" :option="option" style="width: 100%; height: 100%;"></div>
  </div>
</template>

<script>
import HighCharts from 'highcharts';

export default {
  name: 'Chart',
  props: {
    id: {
      type: String,
    },
    option: {
      type: Object,
    },
  },
  watch: {
    option() {
      HighCharts.chart(this.id, this.option);
    },
  },
  mounted() {
    HighCharts.chart(this.id, this.option);
  },
};
</script>

<style scoped>

</style>
