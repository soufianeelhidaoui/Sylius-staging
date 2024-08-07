import Vue from 'vue';

Vue.component('reinsurance', {
  data() {
    return {
      blocks: [] 
    };
  },
  computed  : {
    isMobile() {
        return window.innerWidth <= 768; 
      }
  },
  created() {
    window.addEventListener('resize', this.handleResize);
  },
  destroyed() {
    window.removeEventListener('resize', this.handleResize);
  },
  computed: {
    rearrangedBlocks() {
      if (window.innerWidth <= 767 && this.blocks.length >= 4) {
        let reorderedBlocks = [...this.blocks];
        const temp = reorderedBlocks[2];
        reorderedBlocks[2] = reorderedBlocks[3];
        reorderedBlocks[3] = temp;
        return reorderedBlocks;
      }
      return this.blocks;
    }
  },
  methods: {
    handleResize() {
      this.$forceUpdate();
    }
  }
});
