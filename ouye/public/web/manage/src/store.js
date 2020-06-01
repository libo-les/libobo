import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);

export default new Vuex.Store({
  state: {
    linkIndex: -1,
    linkIndex2: -1,
    linkDataPersonnel: [],
    linkDataManage: [],
    linkDataSetUp: [],
    host: '',
  },
  mutations: {
    linkIndexFun(state, data) {
      state.linkIndex = data;
    },
    linkIndex2Fun(state, data) {
      state.linkIndex2 = data;
    },
    linkData(state, data) {
      state[data.name] = data.value;
    },
  },
  actions: {

  },
});
