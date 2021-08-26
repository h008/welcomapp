<template>
	<div>
		<ul v-if="tags">
			<li v-for="tag in localTags" :key="tag.id">
				<TagForm :tag="tag"
					:tags.sync="localTags" />
			</li>
		</ul>
	</div>
</template>
<script>
import TagForm from './TagForm.vue'
export default {
	name: 'TagList',
	components: {
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
			get() {
				return this.tags
			},
			set(val) {
				this.$emit('update:tags', val)
			},
		},
	},

}
</script>
