<template>
	<div class="group__setting">
		<table>
			<tr>
				<th>グループID</th>
				<th>表示名</th>
				<th>アクション</th>
			</tr>
			<tr v-for="group of localGroups" :key="group.id">
				<td>
					{{ group.id }}
				</td>
				<td>
					<input v-model="group.name" type="text">
				</td>
				<td>
					<button @click="saveGroup(group)">
						保存
					</button>
				</td>
			</tr>
		</table>
	</div>
</template>
<script>
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
export default {
	name: 'GroupEdit',
	components: {
	},
	props: {
		allGroups: {
			type: Array,
			default: () => { return [] },
		},
	},
	computed: {

		localGroups: {
			get() { return this.allGroups },
			set(val) { this.$emit('update:allGrops', val) },
		},
	},
	methods: {
		saveGroup(group) {

			console.info(group)
			const data = { gname: group.name }

			axios.put(generateUrl(`apps/welcomapp/editgroup/${group.id}`), data, { headers: { 'content-type': 'application/json' } }).then((result) => { console.info(result) }).catch((e) => { console.error(e) })
		},
	},
}
</script>
<style scoped>
.group__setting{
	padding:30px;
}
</style>
