<template>
    <el-popover
        width="170"
        @hide="cancel"
        trigger="click"
        :placement="placement">

        <p v-html="message"></p>

        <div class="action-buttons">
            <el-button
                size="mini"
                type="text"
                @click="cancel()">
                cancel
            </el-button>

            <el-button
                type="primary"
                size="mini"
                @click="confirm()">
                confirm
            </el-button>
        </div>

        <template slot="reference">
            <slot name="reference">
                <i class="el-icon-delete"/> OK
            </slot>
        </template>
    </el-popover>
</template>

<script>
    export default {
        emits: ['yes', 'no'],
        name: 'Confirm',
        props: {
            placement: {
                default: 'top-end'
            },
            message: {
                default: 'Are you sure to delete this?'
            }
        },
        data() {
            return {
                visible: true
            }
        },
        methods: {
            hide() {
                this.visible = false;
            },
            confirm() {
                this.hide();
                this.$emit('yes');
            },
            cancel() {
                this.hide();
                this.$emit('no');
            }
        }
    };
</script>
