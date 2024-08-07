import Vue from 'vue';

Vue.component('taxSwitch', {
    template:'<a class="c-tax-switch icon-sorting" @click="toggle">Afficher prix <span v-if="mode===\'ht\'">TTC</span><span v-else>HT</span></a>',
    computed:{
        mode(){
            return this.$store.getters.taxMode()
        }
    },
    methods:{
        toggle(){
            this.$store.commit('taxMode', this.mode === 'ttc' ? 'ht' : 'ttc')
        }
    }
});
