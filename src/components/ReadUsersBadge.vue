<template>
	<div class="usersbadge__wrapper">
		既読：
		<div v-for="username ,index of displayUsers" :key="`usersbd__${index}`">
			<span class="usersbadge">
				<span class="usertext" :style="{'color':black}">{{ username }}</span>
			</span>
			<div />
		</div>
		<div v-if="!displayUsers.length">
			既読ユーザーはまだいません
		</div>
	</div>
</template>
<script>
export default {
	Name: 'ReadUsersBadge',
	props: {
		readUsers: {
			type: Array,
			default: () => { return [] },
		},
		allUsers: {
			type: Array,
			default: () => { return [] },

		},
	},
	computed: {
		displayUsers() {
			return this.allUsers.filter((user) => this.readUsers.includes(user.uid)).map((item) => item.display_name)

		},
	},
}
</script>
<style scoped>
.usersbadge__wrapper{
	display:flex;
	flex-wrap: wrap;
	align-items: flex-start;
}

.usersbadge{
	padding:3px 6px;
	margin-right:8px;
	margin-left:1px;
	border-radius:6px;
	box-shadow:0 0 3px #ddd;
	white-space:nowrap;
}

.usertext{
	font-size: small;
	font-weight: normal;
}
</style>
