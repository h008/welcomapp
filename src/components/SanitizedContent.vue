<template>
	<div>
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div v-html="displayContent" />
	</div>
</template>
<script>
import 'sanitize.css'
export default {
	name: 'SanitizedContent',
	props: {
		content: {
			type: String,
			default: '',
		},
		noteId: {
			type: String,
			default: '',
		},
		author: {
			type: String,
			default: '',
		},
		uid: {
			type: String,
			default: '',
		},
	},
	computed: {
		displayContent() {
			return this.replaceContent(this.content, this.author, this.uid)
		},
	},
	methods: {

		replaceContent(content, authorId, userId) {
			console.info('replace content')

			if (content && authorId !== userId) {
				// TODO

				const beforeStr = `/${authorId}/.announce_`
				const afterStr = `/${userId}/.announce_`
				const reg = new RegExp(beforeStr, 'g')
				content = content.replace(reg, afterStr)
			}
			return content
		},
	},
}
</script>
<style>
#expanddiv li::before {
	content:none !important;
}

</style>
