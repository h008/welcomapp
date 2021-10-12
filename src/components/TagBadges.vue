<template>
	<div class="tagbadge__wrapper">
		<div v-for="tag of displayTags" :key="`tagbd__${tag.id}`">
			<span class="tagbadge" :style="{'background-color':tag.color}">
				<span class="tagtext" :style="{'color':tag.color}">{{ tag.tag_name }}</span>
			</span>
			<div />
		</div>
	</div>
</template>
<script>
export default {
	Name: 'TagBadges',
	props: {
		tags: {
			type: Array,
			default: () => { return [] },
		},
		displayTagIds: {
			type: String,
			default: '',
		},
	},
	computed: {
		displayTags() {
			const tagIds = this.displayTagIds.split(',').map((elm) => Number(elm))
			if (!tagIds || !this.tags || !this.tags.length) { return [] }
			return this.tags.filter((tag) => tagIds.includes(Number(tag.id)))

		},
	},
}
</script>
<style scoped>
.tagbadge__wrapper{
	display:flex;
	flex-wrap: wrap;
	align-items: flex-start;
}

.tagbadge{
	padding:3px 6px;
	margin-right:8px;
	margin-left:1px;
	border-radius:6px;
	box-shadow:0 0 3px #ddd;
	white-space:nowrap;
}

.tagtext{
	filter:invert(100%) grayscale(100%) contrast(100);
	font-size: medium;
	font-weight: bold;
}
</style>
