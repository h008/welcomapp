<template>
	<div v-if="localCategory " class="wrapper">
		<div class="d-flex">
			<input v-model="localCategory.category_name" type="text">
			<ColorPicker v-model="localCategory.color">
				<button :style="{'background-color':localCategory.color}">
					カラーを選択
				</button>
			</ColorPicker>
			<input type="button" value="保存" @click="saveItem">
			<input v-if="localCategory.id !== -1"
				type="button"
				value="削除"
				@click="removeItem">
		</div>
		{{ localCategory.color }}
	</div>
</template>
<script>
import Mymodules from '../js/modules'
import ColorPicker from '@nextcloud/vue/dist/Components/ColorPicker'
export default {
	name: 'CategoryForm',
	components: {
		ColorPicker,
	},
	props: {
		category: {
			type: Object,
			default: () => { return { id: -1, category_name: '', category_order: 99, color: '' } },
		},
		categories: {
			type: Array,
			default: () => { return [] },
		},
	},
	computed: {
		localCategory: {
			get() { return this.category },
			set(val) { this.$emit('update:category', val) },

		},
	},
	methods: {
		saveItem() {
			if (!this.localCategory.id) {
				this.localCategory.id = -1
			}
			if (!this.localCategory.category_order) {
				this.localCategory.category_order = 999
			}
			if (!this.localCategory.category_name) {
				this.localCategory.category_name = '名称未設定'
			}
			if (!this.localCategory.color) {
				this.localCategory.color = ''
			}
			 Mymodules.saveCategory(this.localCategory).then((result) => {
				const tmpData = result.data

				const tmpArray = this.categories.filter((category) => category.id !== tmpData.id)
				tmpArray.push(tmpData)
				tmpArray.sort((a, b) => {
					if (a.order < b.order) { return -1 }
					if (a.order > b.order) { return 1 }
					if (a.id < b.id) { return -1 }
					if (a.id > b.id) { return 1 }
					return 0
			 })
				this.$emit('update:categories', tmpArray)
			})

		},
		removeItem() {
			if (confirm('削除します')) {
				Mymodules.deleteCategory(this.category).then((result) => {
					const tmpArray = this.categories.filter((category) => category.id !== this.category.id)
					this.$emit('update:categories', tmpArray)
				})

			} else {
				alert('キャンセルしました')

			}
		},
	},

}
</script>
<style scoped>
.wrapper{
	display: inline-block;
}

.d-flex {
	display:flex;
}
</style>
