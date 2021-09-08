<template>
	<div class="category__setting">
		<CategoryList :categories.sync="localCategories" />
		<CategoryForm :category="{id:-1,category_name:'',category_order:99,color:''}" :categories.sync="localCategories" />
	</div>
</template>
<script>
import CategoryList from './CategoryList.vue'
import CategoryForm from './CategoryForm.vue'
export default {
	name: 'CategorySetting',
	components: {
		CategoryList,
		CategoryForm,
	},
	props: {
		categories: {
			type: Array,
			default: () => { return [] },
		},
	},
	computed: {

		localCategories: {
			get() { return this.categories },
			set(val) { this.$emit('update:categories', val) },
		},
	},
	methods: {
	},
}
</script>
<style scoped>
.category__setting{
	padding:30px;
}
</style>
