<template>
    <div class="dashboard box_wrapper">
        <div class="box dashboard_box box_narrow">
            <div class="box_header" style="padding: 20px 15px;font-size: 16px;">
                Good {{ greetingTime }} {{ me.full_name }}!
            </div>
        </div>
    </div>
</template>

<script type="text/babel">
    export default {
        name: 'Dashboard',
        data() {
            return {
                me: this.appVars.me,
            }
        },
        computed: {
            greetingTime() {
                const m = this.moment();
                let g = null; //return g

                if (!m || !m.isValid()) {
                    return;
                } //if we can't find a valid or filled moment, we return.

                const split_afternoon = 12 //24hr time to split the afternoon
                const split_evening = 17 //24hr time to split the evening
                const currentHour = parseFloat(m.format("HH"));

                if (currentHour >= split_afternoon && currentHour <= split_evening) {
                    g = "afternoon";
                } else if (currentHour >= split_evening) {
                    g = "evening";
                } else {
                    g = "morning";
                }

                return g;
            }
        }
    };
</script>
