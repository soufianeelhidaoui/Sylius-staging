import Vue from 'vue';

Vue.component('navigation', {
    props:['type'],
    data(){
        return{
            visible: false
        }
    },
    methods:{
        close(){
            this.visible = false

            this.$close('nav-'+this.type)

            this.$children.forEach(($children)=>{
                if( $children.$options.name === 'navigation' && $children.visible )
                    this.$close('nav-'+$children.type)
            })
        },
        open(e){
            if( !this.visible ){

                if( e )
                    e.preventDefault()

                this.$trigger('hide-nav-'+this.type);

                this.visible = true

                document.body.classList.add('has-nav-'+this.type)
            }
        },
        hide(){
            console.log('ici')
            this.visible = false
        }
    },
    mounted(){
        window.addEventListener("scroll", this.close)
        this.$listen('open-nav-'+this.type, this.open)
        this.$listen('hide-nav-'+this.type, this.hide)
    },
    destroyed(){
        window.removeEventListener("scroll", this.close)
    }
});
