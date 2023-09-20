<script>
    import {router} from '@inertiajs/vue3'
    import handlePagination from "./handlePagination";
    import Pusher from "pusher-js";
    export default {
        props  : {
            goodsProp: Object
        },
        computed: {
            handlePagination() {
                const handlePaginationValue = handlePagination(this.goods);

                return {...handlePaginationValue};
            }
        },
        data() {
            return {
                goods: this.goodsProp,
                form: {
                    file: null,
                },
                importedNum: 0,
                skippedNum: 0,
                resultMsg: '',
                started: false
            }
        },
        mounted() {
            //this.goods=this.goodsProp;
            //Pusher.logToConsole = true;
            var pusher = new Pusher('39b20520de09404b1f83', {
                cluster: 'eu'
            });
            var ref=this;
            var channel = pusher.subscribe('import');
            channel.bind('imported', function(data) {
                ref.importedNum=data.addedNumber;
                ref.skippedNum=data.skippedNumber;
            });

        },
        methods: {
            submit: function () {
                let data = new FormData;
                this.started=true;
                this.resultMsg='';
                this.importedNum=0;
                this.skippedNum=0;
                data.append('file', this.form.file);
                var ref=this;
                axios.post("/goods/import", data)
                    .then( (response) => {
                        ref.resultMsg=response.data;
                        console.log(response.data)
                        ref.started=false;
                    }).catch(
                    function (error) {
                        ref.resultMsg=error.response.data.message;
                        console.log(error.response.data.message);
                        //return Promise.reject(error)
                    }
                )

                //router.post('/goods/import', this.form)
            },
            file(event) {
                this.form.file=event.target.files[0];
            },
            refresh(){
                router.get('/goods')
            },
            clear(){
                router.post('/goods/clear')
                setTimeout(function (){window.location='/goods'}, 3000);
            }
        },
    }

</script>

<template>
    <div>
        <h1>Welcome</h1>
        <p>
            This is test task of Roman Prisiazhniuk for job opportunity in Redentu Company<br>
            Purpose of the task is to import xlsx file containing wares data
        </p>
        <h3>Please select a xlsx file with wares data and press "Submit"</h3>
        <form @submit.prevent="this.submit" enctype='multipart/form-data' method="post">
            <label for="file">
                Importing file name:
            </label>
            <input type='file' id="file" @change="file">
            <button type="submit">
                Submit
            </button>
        </form>
        <p v-if="!this.started && this.goods.length">
            <div>You can clear db to run import again</div>
            <button @click="clear">Clear db</button>
        </p>
        <p v-if="!this.started">
            <button @click="refresh">Show Wares</button>
        </p>
        <p>
            <div class="msg" v-if="this.started">Starting import!</div>
            <div class="msg" v-if="this.importedNum">Imported num: {{ this.importedNum }}</div>
            <div class="msg" v-if="this.skippedNum">Skipped num: {{ this.skippedNum }}, reason: duplicate</div>
            <div class="msg">{{ this.resultMsg }}</div>
        </p>
        <table class='goods'>
            <tr class="goodHeader" v-if="this.goods.length">
                <td>
                    Manufacturer
                </td>
                <td>
                    Name
                </td>
                <td>
                    Desc
                </td>
                <td>
                    Price
                </td>
                <td>
                    Warranty
                </td>
                <td>
                    Rubrics
                </td>
                <td>
                    Category
                </td>
            </tr>
            <tr class='good' v-for="(good,key) in this.handlePagination.paginatedData.value" :key="key">
                <td class="col" v-for="(col,keyCol) in good" :key="keyCol">
                    {{ col }}
                </td>
            </tr>
        </table>
        <button v-if="this.goods.length" @click="this.handlePagination.backPage">prev</button>
        <button
            v-for="item in Math.ceil(this.goods.length / this.handlePagination.perPage)"
            :key="item"
            @click="() => this.handlePagination.goToPage(item)"
        >
            {{ item }}
        </button>
        <button v-if="this.goods.length" @click="this.handlePagination.nextPage">next</button>
    </div>
</template>
