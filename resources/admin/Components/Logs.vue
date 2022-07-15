<template>
    <div class="box_wrapper">
        <div class="box dashboard_box">
            <div class="box_header" style="padding: 20px 15px;font-size: 16px;">
                Logs
                <el-button @click="fetchLogs()" size="mini">refresh</el-button>
            </div>
            <div class="log_filters">
                <div class="log_filter">
                    <el-switch @change="fetchLogs()" active-value="yes" inactive-value="no" v-model="show_ignores"/>
                    Show Ignores
                </div>
                <div class="log_filter">
                    <label class="filter_title">Component</label>
                    <el-select @change="fetchLogs()" size="mini" clearable filterable v-model="filter.context">
                        <el-option v-for="item in components" :key="item" :value="item" :label="item"></el-option>
                    </el-select>
                </div>
            </div>
            <el-table @sort-change="handleSortChange"
                      :default-sort="{ prop: sortBy, order: sortType }"
                      v-loading="loading"
                      :data="logs"
                      :row-class-name="tableRowClassName"
                      style="width: 100%"
            >
                <el-table-column type="expand">
                    <template #default="props">
                        <div style="padding: 10px 25px;">
                            <b>Backtrace</b>
                            <pre class="sql_pre">{{ props.row.trace }}</pre>
                            <hr />
                            <p>
                                This query has been called <code>{{props.row.total_counter}}</code> times since <code>{{props.row.created_at}}</code>
                            </p>
                            <p>Last Reference URL: <code>{{props.row.last_url}}</code></p>
                        </div>
                    </template>
                </el-table-column>
                <el-table-column sortable prop="updated_at" label="Date" width="190"/>
                <el-table-column label="SQL">
                    <template #default="scope">
                        <pre class="sql_pre">{{ scope.row.sql }}</pre>
                    </template>
                </el-table-column>
                <el-table-column prop="caller" label="Caller" width="230">
                    <template #default="scope">
                        <span style="font-size:12px; line-height: 12px;">{{ scope.row.caller }}</span>
                    </template>
                </el-table-column>
                <el-table-column sortable prop="ltime" label="Timing" width="120"/>
                <el-table-column sortable prop="result" label="Result" width="90"/>
                <el-table-column sortable prop="context" label="Component" width="120"/>
                <el-table-column fixed="right" label="Action" width="90">
                    <template #default="scope">
                        <el-button @click="changeStatus(scope.row.id, 'ignored', scope.$index)"
                                   v-if="scope.row.status == 'new'" size="mini" type="danger" plain>ignore
                        </el-button>
                        <el-button @click="changeStatus(scope.row.id, 'new', scope.$index)" v-else size="mini"
                                   type="success" plain>re-watch
                        </el-button>
                    </template>
                </el-table-column>
            </el-table>
            <el-row style="margin-top: 20px;" :gutter="30">
                <el-col :md="12" :xs="24">
                    <el-popconfirm @confirm="deleteAllLogs()" title="Are you sure to delete all the logs?">
                        <template #reference>
                            <el-button v-loading="deleting" size="mini" type="danger">Delete All Logs</el-button>
                        </template>
                    </el-popconfirm>
                </el-col>
                <el-col :md="12" :xs="24">
                    <div class="fql_pagi text-align-right">
                        <el-pagination @current-change="changePage"
                                       :current-page="paginate.page"
                                       :page-size="paginate.per_page"
                                       background layout="prev, pager, next"
                                       :total="paginate.total"
                        />
                    </div>
                </el-col>
            </el-row>
        </div>
    </div>
</template>

<script type="text/babel">
import Confirm from "@/admin/Pieces/Confirm";

export default {
    name: 'Logs',
    components: {
        Confirm
    },
    data() {
        return {
            logs: [],
            paginate: {
                page: 1,
                per_page: 20,
                total: 0
            },
            filter: {
                context: ''
            },
            show_ignores: 'no',
            statuses: this.appVars.log_filters.statuses,
            components: this.appVars.log_filters.components,
            loading: false,
            sortBy: 'updated_at',
            sortType: 'descending',
            deleting: false
        }
    },
    methods: {
        changePage(page) {
            this.paginate.page = page;
            this.fetchLogs();
        },
        deleteAllLogs() {
            this.deleting = true;
            this.$post('logs/truncate')
                .then(response => {
                    this.$notify.success(response.message);
                    this.fetchLogs();
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .always(() => {
                    this.deleting = false;
                });
        },
        handleSortChange(prop) {
            if (!prop.prop) {
                return;
            }
            this.sortBy = prop.prop;
            this.sortType = prop.order;
            this.fetchLogs();
        },
        changeStatus(rowId, newStatus, index = false) {
            this.$put(`logs/${rowId}`, {
                status: newStatus
            })
                .then(response => {
                    this.fetchLogs();
                })
                .catch((errors) => {
                    this.$handleError(errors);
                    this.fetchLogs();
                });

        },
        fetchLogs() {
            this.loading = true;
            this.$get('logs', {
                per_page: this.paginate.per_page,
                page: this.paginate.page,
                filters: this.filter,
                sortBy: this.sortBy,
                sortType: (this.sortType == 'descending') ? 'DESC' : 'ASC',
                show_ignores: this.show_ignores
            })
                .then(response => {
                    this.logs = response.logs.data;
                    this.paginate.total = response.logs.total;
                })
                .catch((errors) => {
                    this.$handleError(errors);
                })
                .always(() => {
                    this.loading = false;
                });
        },
        tableRowClassName({row, rowIndex}) {
            if(row.ltime >= this.appVars.db_expensive) {
                return 'row_expensive';
            }
            return '';
        }
    },
    mounted() {
        this.fetchLogs();
    }
}
</script>
