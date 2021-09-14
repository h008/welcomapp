<template>
	<div>
		<ul v-if="fileInfo && fileInfo.length">
			<span v-for="file of fileInfo" :key="file.herf">
				<ListItem v-if="canShowItem(file)"
					:avatar-size="24"
					:no-margin="true"
					:title="file.filename"
					:bold="file.exist"
					@click="openItem(file)">
					<template slot="icon">
						<Avatar v-if="!file.exist" icon-class="icon-filetype-file" />
						<Avatar v-else-if="file.filetype=='folder'" icon-class="icon-folder" />
						<Avatar v-else-if="file.filetype.search(/image/) !== -1 " icon-class="icon-picture" />
						<Avatar v-else icon-class="icon-file" />
					</template>
					<template slot="actions">
						<ActionLink v-if="!file.exist" icon="icon-folder" />
						<ActionLink v-else-if="file.filetype=='folder'" icon="icon-folder" :href="downloadFile(file.id)" />
						<ActionLink v-else
							icon="icon-download"
							:href="downloadFile(file.id)"
							:download="file.filename" />
						<ActionLink icon="icon-external" :href="file.userRef" />
					</template>
				</ListItem>
			</span>
		</ul>
	</div>
</template>
<script>
import dayjs from 'dayjs'
// import axios from '@nextcloud/axios'
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'
import ActionLink from '@nextcloud/vue/dist/Components/ActionLink'
import { generateUrl } from '@nextcloud/router'
export default {
	name: 'FileList',
	components: {
		ListItem,
		Avatar,
		ActionLink,
	},
	props: {
		fileInfo: {
			type: Array,
			default: () => { return [] },
		},
	},
	computed: {

	},
	methods: {
		getFileName(href) {
			const path = decodeURI(href)
			const splitPath = path.split('/')
			return splitPath.pop()

		},
		getdate(date) {
			return dayjs(date).format('YYYY/MM/DD HH:mm:ss')
		},
		downloadFile(fileId) {
			// return generateUrl(`/f/${fileId}`)
			// const data = { fileId }

			// axios.get('/ocs/v2.php/apps/files_sharing/api/v1/shares/6', data, { headers: { 'OCS-APIRequest': true } }).then((result) => {
			  // //console.info(result.data.ocs.data)
			  // })
			return generateUrl(`/f/${fileId}`)
		},
		openItem(file) {
			if (file.exist) {
			  window.location.href = file.userRef

			}

		},
		canShowItem(note) {
			if (note.filename === `.announce_${note.fileurl}`) {
				return false
			}
			if (!note.is_eycatch || Number(note.is_eyecatch) === 0 || note.is_eyecatch === 'false') {
				return true
			}
			if (note.is_eyecatch || Number(note.is_eyecatch) === 1 || note.is_eyecatch === 'true') {
				return false
			}
			return true
		},
	},

}
</script>
