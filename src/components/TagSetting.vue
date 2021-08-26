<template>
	<div>
		<TagList :tags.sync="localTags" />
		<TagForm :tag="{id:-1,tag_name:'',tag_order:99,color:''}" :tags.sync="localTags" />
	</div>
</template>
<script>
import TagList from './TagList.vue'
import TagForm from './TagForm.vue'
export default {
	name: 'TagSetting',
	components: {
		TagList,
		TagForm,
	},
	props: {
		tags: {
			type: Array,
			default: () => { return [] },
		},
	},
	computed: {

		localTags: {
			get() { return this.tags },
			set(val) { this.$emit('update:tags', val) },
		},
	},
	methods: {
	},
}
</script>
