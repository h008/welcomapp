<template>
	<div>
		<!-- eslint-disable-next-line vue/no-v-html -->
		<div v-html="content" />
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
	},
}
</script>
