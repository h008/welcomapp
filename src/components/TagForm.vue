<template>
	<div v-if="localTag " class="wrapper">
		<div class="d-flex">
			<input v-model="localTag.tag_name" type="text">
			<ColorPicker v-model="localTag.color">
				<button :style="{'background-color':localTag.color}">
					カラーを選択
				</button>
			</ColorPicker>
			<input type="button" value="保存" @click="saveItem">
			<input v-if="localTag.id !== -1"
				type="button"
				value="削除"
				@click="removeItem">
		</div>
	</div>
</template>
<script>
import Mymodules from '../js/modules'
import ColorPicker from '@nextcloud/vue/dist/Components/ColorPicker'
export default {
	name: 'TagForm',
	components: {
		ColorPicker,
	},
	props: {
		tag: {
			type: Object,
			default: () => { return { id: -1, tag_name: '', tag_order: 99, color: '' } },
		},
		tags: {
			type: Array,
			default: () => { return [] },
		},
	},
	computed: {
		localTag: {
			get() { return this.tag },
			set(val) { this.$emit('update:tag', val) },

		},
	},
	methods: {
		saveItem() {
			if (!this.localTag.id) {
				this.localTag.id = -1
			}
			if (!this.localTag.tag_order) {
				this.localTag.tag_order = 999
			}
			if (!this.localTag.tag_name) {
				this.localTag.tag_name = '名称未設定'
			}
			if (!this.localTag.color) {
				this.localTag.color = ''
			}
			 Mymodules.saveTag(this.localTag).then((result) => {
				const tmpData = result.data

				const tmpArray = this.tags.filter((tag) => tag.id !== tmpData.id)
				tmpArray.push(tmpData)
				tmpArray.sort((a, b) => {
					if (a.order < b.order) { return -1 }
					if (a.order > b.order) { return 1 }
					if (a.id < b.id) { return -1 }
					if (a.id > b.id) { return 1 }
					return 0
			 })
				this.$emit('update:tags', tmpArray)
			})

		},
		removeItem() {
			if (confirm('削除します')) {
				Mymodules.deleteTag(this.tag).then((result) => {
					const tmpArray = this.tags.filter((tag) => tag.id !== this.tag.id)
					this.$emit('update:tags', tmpArray)
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
