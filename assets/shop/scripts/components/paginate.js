import Vue from "vue";
import VRuntimeTemplate from "v-runtime-template";

Vue.component("paginate", {
  props: ["init", "per_page", "page_size", "total", "max"],
  components: {
    VRuntimeTemplate,
  },
  data() {
    return {
      parentComponent: this,
      page: 0,
      productsHtml: [],
      params: {
        "filter.v.price.gte": false,
        "filter.v.price.lte": false,
        "filter.v.price.couleur": false,
      },
      maxPrix: localStorage.getItem("max"),
    };
  },
  computed: {
    count() {
      return this.per_page * this.page + this.init;
    },
  },
  methods: {
    loadMore() {
      this.page++;
    },
    loadPaged(url) {
      this.$http
        .get(url, {
          params: { view: "paged" },
          headers: { accept: "text/html" },
        })
        .then((response) => {
          this.productsHtml.push(response.body);
        });
    },

    shouldAddToLocalStorage() {
      // Check if the current path contains 'max_price'
      let currentPath = window.location.href;
      if (currentPath.includes("max_price") || currentPath.includes("price")) {
        console.log("this shouldn t be added to local storage");
        return false;
      } else if (this.total === 0 && !currentPath.includes("max_price")) {
        return false;
      }
      return true; // Add to localStorage if 'max_price' is not in the path
    },
  },
  mounted() {
  
    if (this.max && this.shouldAddToLocalStorage()) {
      localStorage.setItem("max", this.max);
      console.log("Added max to localStorage:", this.max);
    }

    for (let query of urlParams.entries())
      this.params[query[0]] =
        query[0] === "filter.v.price.gte" || query[0] === "filter.v.price.lte"
          ? parseInt(query[1])
          : query[1];
  },
});
