<template>
  <div class="section">
    <settingsRow
      v-for="(option, key) in optionRows"
      v-bind:key="key"
      :value="option.value"
      :id="option.id"
      :label="option.label"
      :placeholder="option.placeholder"
      :path="option.path"
    />
  </div>
  <div class="section">
    <toggleButton
      :defaultStatus="pStatus"
      disabledText="No Aria2 Settings for non-admin Users"
      enabledText="No Aria2 Settings for non-admin Users"
      @changed="toggle"
      name="disallow_aria2_settings"
    ></toggleButton>
  </div>
  <customOptions
    name="admin-aria2-settings"
    @mounted="render"
    title="Global Aria2 Settings"
    path="/apps/ncdownloader/admin/aria2/save"
    :validOptions="validOptions"
  >
    <template #save>Save Settings</template>
  </customOptions>
</template>
<script>
import customOptions from "./components/customOptions";
import helper from "./utils/helper";
import aria2Options from "./utils/aria2Options";
import settingsRow from "./components/settingsRow";
import toggleButton from "./components/toggleButton";

export default {
  name: "adminSettings",
  data() {
    return {
      options: [],
      validOptions: aria2Options,
      settings: [],
      pStatus: false,
    };
  },
  components: {
    customOptions,
    settingsRow,
    toggleButton,
  },
  methods: {
    toggle(name, value) {
      let data = {};
      data[name] = value ? 1 : 0;
      let path = "/apps/ncdownloader/admin/save";
      const url = helper.generateUrl(path);
      helper
        .httpClient(url)
        .setData(data)
        .setHandler((resp) => {
          if (resp["message"]) {
            helper.message(helper.t(resp["message"]), 1000);
          }
        })
        .send();
    },
    render(event, $vm) {
      helper
        .httpClient(helper.generateUrl("/apps/ncdownloader/admin/aria2/get"))
        .setMethod("GET")
        .setHandler((data) => {
          if (!data) {
            return;
          }
          let input = [];
          for (let key in data) {
            if (aria2Options.includes(key))
              input.push({ name: key, value: data[key], id: key });
          }
          //settingsForm.getInstance($vm.container).render(input);
          $vm.options = input;
        })
        .send();
    },
  },
  computed: {
    optionRows() {
      return this.options;
    },
  },
  mounted() {
    try {
      let data = this.$el.parentElement.getAttribute("data-settings");
      data = JSON.parse(data);
      this.settings = data;
      this.pStatus = helper.str2Boolean(data["disallow_aria2_settings"]);
      let path = "/apps/ncdownloader/admin/save";
      this.options = [
        {
          label: "Aria2 RPC Host",
          id: "ncd_aria2_rpc_host",
          value: data.ncd_aria2_rpc_host,
          placeholder: "127.0.0.1",
          path: path,
        },
        {
          label: "Aria2 RPC Port",
          id: "ncd_aria2_rpc_port",
          value: data.ncd_aria2_rpc_port,
          placeholder: "6800",
          path: path,
        },
        {
          label: "Aria2 RPC Token",
          id: "ncd_aria2_rpc_token",
          value: data.ncd_aria2_rpc_token,
          placeholder: data.ncd_aria2_rpc_token
            ? data.ncd_aria2_rpc_token
            : "ncdownloader123",
          path: path,
        },
        {
          label: "Youtube-dl binary",
          id: "ncd_yt_binary",
          value: data.ncd_yt_binary,
          placeholder: data.ncd_yt_binary
            ? data.ncd_yt_binary
            : "/usr/local/bin/youtube-dl",
          path: path,
        },
        {
          label: "Aria2c binary",
          id: "ncd_aria2_binary",
          value: data.ncd_aria2_binary,
          placeholder: "/usr/local/bin/aria2c",
          path: path,
        },
      ];
    } catch (e) {
      helper.error(e);
    }
  },
};
</script>
