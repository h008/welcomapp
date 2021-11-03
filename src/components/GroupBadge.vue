<template>
	<div class="d-flex">
		<div v-for="group of groups" :key="group.id" class="shared_groups">
			{{ group.name }}
		</div>
	</div>
</template>
<script>
export default {
	name: 'GroupBadge',
	props: {
		groups: {
			type: Array,
			default: () => { return [] },
		},
	},
}
</script>
<style scoped>
.d-flex{
	display:flex;
	float:left;

}

.shared_groups{
	border-radius: 5px;
	border:gray solid 1px;
	padding-inline: 3px;
	margin-inline: 2px;

}
</style>
