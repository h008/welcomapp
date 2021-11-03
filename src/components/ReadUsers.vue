<template>
	<div>
		<div class="readusers__wrapper">
			<input v-if="isRead==='9999'"
				type="button"
				value="既読にする"
				@click="markAsRead">
			<input v-else
				type="button"
				value="未読に戻す"
				@click="markAsUnread">
		</div>
		<ReadUsersBadge :read-users="readUsers" :all-users="allUsers" />
		<div />
	</div>
</template>

<script>
import ReadUsersBadge from './ReadUsersBadge.vue'
export default {
	name: 'ReadUsers',
	components: {
		ReadUsersBadge,
	},
	props: {
		readUsers: {
			type: Array,
			default: () => {
				return []
			},
		},
		allUsers: {
			type: Array,
			default: () => {
				return []
			},
		},
		user: {
			type: Object,
			default: () => {
				return {}
			},
		},
		isRead: {
			type: String,
			default: '9999',
		},
	},

	data() {
		return {
		}
	},
	methods: {
		markAsRead() {
			const tmpUsersArray = this.readUsers
			tmpUsersArray.push(this.user.id)
			this.$emit('update:read-users', tmpUsersArray)
		},
		markAsUnread() {
			const tmpUsersArray = this.readUsers.filter((uid) => (uid !== this.user.id))
			this.$emit('update:read-users', tmpUsersArray)

		},
	},
}
</script>

<style scoped>
.readusers__wrapper {
	display:flex;
	justify-content: flex-end;
}
</style>
