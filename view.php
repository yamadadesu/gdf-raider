<?php


//課題
/**
 * レイド複数選択した時に番号若いほうを優先してとってる？
 * 表示件数選択出来るようにする
 * こぴー機能いれる
 */

?>

<html>
<head>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="https://unpkg.com/vue/dist/vue.js"></script>
</head>
<body>

<div style="margin-top:10px;"></div>

<div id="raids">
    <select v-model="selected" v-on:change="hoge" multiple>
      <option v-for="option in options" v-bind:value="option.value">
        {{ option.text }}
      </option>
    </select>
    <span>Selected: {{ selected }}</span>

    <div style="margin-top:15px;"></div>

    <table border=1>
        <thead>
            <tr>
                <th>日時</th>
                <th>コメント</th>
                <th>バトル名</th>
                <th>参戦ID</th>
                <th>コピーする</th>
            </tr>
            <tr v-for="raid in raids">
                <td>{{raid.created_at}}</td>
                <td>{{raid.comment}}</td>
                <td>{{raid.name}}</td>
                <td>{{raid.id}}</td>
                <td><input type="button" value="copy"></td>
            </tr>

    </table>
</div>

<script>

let raids = new Vue({
    el: '#raids',
    data: {
        selected: [],
        options: [
          { text: 'Lv50 ティアマトマグナ', value: '1' },
          { text: 'Lv60 リヴァイアサン・マグナ', value: '2' },
          { text: 'Lv60 ユグドラシルマグナ', value: '3' },
          { text: 'Lv100 ゼノ・イフリート', value: '4' }
        ],
        raids: [],
        displayed: [],
    },
    methods: {
        loadData: function () {
            let data = {
                selected: this.selected,
                displayed: this.displayed
            };

            console.info(data);

            ajax(data).then( response => {
                if(response.data) {
                    Object.keys(response.data).forEach( key => {
                        if( this.raids.length  < 20 ) {
                            this.raids.unshift(response.data[key]);
                            this.displayed.unshift(response.data[key]["id"]);
                        } else {
                            this.raids.pop().unshift(response.data[key]);
                            this.displayed.pop().unshift(response.data[key]["id"]);
                        }
                    });
                }

            })
            .catch( error => {
                console.error(error);
            });
        }
    },
    mounted: function () { //v2系はreadyじゃアカンらしいよ
        this.$nextTick(function () {
            this.loadData();

           setInterval(function () {
                this.loadData();
            }.bind(this), 3000);
        })
    }
})

function ajax(data) {
    return axios.get("ajax.php", { params: data  });
}
</script>

</body>
</html>
