<template>
    <div class="dashboard box_wrapper">
        <div class="box dashboard_box box_narrow">
            <div v-loading="loading" class="box_header" style="padding: 20px 15px;font-size: 16px;">
                Settings
            </div>
            <div v-if="settings" class="box_body">
                <el-form :data="settings" label-position="top">
                    <el-form-item label="Enable Database Query Logging">
                        <el-checkbox true-label="yes" v-model="settings.active" false-label="no">Enable Database Query Logging</el-checkbox>
                    </el-form-item>
                    <el-form-item v-if="settings.active == 'yes'">
                        <h3>Please which plugins (or core) you want to track for database query logging</h3>
                        <el-checkbox-group v-model="settings.modules">
                            <el-checkbox label="core"><b>WordPress Core DB Queries</b></el-checkbox>
                            <el-checkbox v-for="plugin in plugins" :label="plugin" :key="plugin" />
                        </el-checkbox-group>
                    </el-form-item>
                    <el-form-item>
                        <el-button @click="saveSettings()" :disabled="saving" v-loading="saving" type="primary">Save Settings</el-button>
                    </el-form-item>
                    <el-form-item v-if="settings.active != 'yes' || !settings.modules.length">
                        <p style="color: red;">Based on your current settings no query will be logged</p>
                    </el-form-item>
                </el-form>
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
export default {
    name: 'Settings',
    data() {
        return {
            settings: false,
            plugins: [],
            loading: false,
            activated: false,
            saving: false
        }
    },
    methods: {
        fetchSettings() {
            this.loading = true;
            this.$get('logs/settings')
                .then(response => {
                    this.activated = response.activated;
                    this.settings = response.settings;
                    this.plugins = response.plugins;
                })
                .catch((errors) => {
                    this.$handleError(errors)
                })
                .always(() => {
                    this.loading = false;
                });
        },
        saveSettings() {
            this.saving = false;
            this.$post('logs/settings', {
                settings: this.settings
            })
                .then(response => {
                   this.$notify.success(response.message);
                   this.settings = response.settings;
                   this.appVars.is_active = true;
                })
                .catch((errors) => {
                    this.$handleError(errors)
                })
                .always(() => {
                    this.saving = false;
                });
        }
    },
    mounted() {
        this.fetchSettings();
    }
};
</script>
