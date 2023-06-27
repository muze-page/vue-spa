//vite/dist/index.js
//console.table(dataLocal);

const App = {
  setup() {
    //初始值
    const datas = Vue.reactive({
      dataOne: "",
      dataTwo: "",
    });

    //获取数据
    const vuespa_get_option = () => {
      axios
        .post(dataLocal.route + "pf/v1/get_option", datas, {
          headers: {
            "X-WP-Nonce": dataLocal.nonce,
            "Content-Type": "application/json",
          },
        })
        .then((response) => {
          const data = response.data;
          datas.dataOne = data.dataOne;
          datas.dataTwo = data.dataTwo;
        })
        .catch((error) => {
          window.alert("连接服务器失败或后台读取出错！数据读取失败");
          console.log(error);
        });
    };

    //保存数据
    const vuespa_update_option = () => {
      axios
        .post(dataLocal.route + "pf/v1/update_option", datas, {
          headers: {
            "X-WP-Nonce": dataLocal.nonce,
          },
        })
        .then((response) => {
          alert("保存成功");
        })
        .catch((error) => {
          alert("保存失败");
          console.log(error);
        });
    };

    //页面初始加载
    Vue.onMounted(() => {
      console.log("简简单单");
      vuespa_get_option();
    });

    return { datas, vuespa_update_option };
  },
  template:
    '文本框1：<input type="text" v-model="datas.dataOne"><br/>文本框2：<input type="text" v-model="datas.dataTwo"><hr/><button class="button button-primary" @click="vuespa_update_option">保存</button>',
};

Vue.createApp(App).mount("#vuespa");
