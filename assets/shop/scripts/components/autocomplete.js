import Vue from 'vue';

Vue.component('autocomplete', {
    methods:{
        match(s){

            s = s.toLowerCase().replace('-','')
            let q = this.q.toLowerCase().replace('-','')

            return q.length >= 2 && s.indexOf(q) >= 0
        },
        focus(){
            this.focused = true
        },
        unfocus(){
            this.focused = false
        }
    },
    data(){
        return{
            q: '',
            focused: false
        }
    },
    mounted() {
        document.body.addEventListener('click', e=>{
            if( this.focused && this.q.length ){
                if( !this.$el.contains(e.target) )
                    this.unfocus();
            }
        })
    }
});
