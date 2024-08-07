import Vue from 'vue';

Vue.component('sort', {
    props:['title','titlePrefix'],
    template: '<div class="c-sort">' +
        '<a class="button button--thin icon_after-chevron-down" @click="show=!show"><b>{{ title }}</b></a>' +
        '<div class="c-sort__options" v-if="show"><slot></slot></div>' +
        '</div>',
    data(){
        return{
            show: false
        }
    },
    mounted() {
        document.body.addEventListener('click', e=>{
            if( this.show ){
                if( !this.$el.contains(e.target) )
                    this.show = false;
            }
        })
    }
});
