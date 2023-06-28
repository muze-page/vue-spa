//vite/dist/index.js
console.log(dataLocal.data.user);
const App = {
  setup() {
    

    //存储传来的值
    const siteData = dataLocal.data;

    //存储选项值
    const datas = Vue.reactive({
      dataOne: "",
      dataTwo: "",
      dataName: [],
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
          datas.dataName = data.dataName;
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

    return { datas, siteData, vuespa_update_option };
  },
  template: `
  文本框1：<input type="text" v-model="datas.dataOne"><br/>
  文本框2：<input type="text" v-model="datas.dataTwo"><hr/>
  
  用户选择：<select v-model="datas.dataName" multiple>
  <option v-for="option in siteData.user" :key="option.id" :value="option.id">
      {{ option.name }}
  </option>
</select>
<p>你选择了：{{ datas.dataName }}</p><br/>
按住command(control)按键即可进行多选<hr/>
    <button class="button button-primary" @click="vuespa_update_option">保存</button>`,
};

Vue.createApp(App).mount("#vuespa");
