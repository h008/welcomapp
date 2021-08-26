<template>
	<div>
		<ul v-if="categories">
			<li v-for="category in localCategories" :key="category.id">
				<CategoryForm :category="category"
					:categories.sync="localCategories" />
			</li>
		</ul>
	</div>
</template>
<script>
import CategoryForm from './CategoryForm.vue'
export default {
	name: 'CategoryList',
	components: {
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
			get() {
				return this.categories
			},
			set(val) {
				this.$emit('update:categories', val)
			},
		},
	},

}
</script>
