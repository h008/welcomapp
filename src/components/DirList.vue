<template>
	<div class="align-left">
		<ul>
			<ListItem v-for="missingItem of missingItems"
				:key="missingItem.id"
				:bold="true"
				:title="missingItem.filename">
				<template slot="icon">
					<Avatar icon-class="icon-filetype-file" />
				</template>
				<template slot="subtitle">
					ファイルが見つかりません。削除してから再アップロードしてください。
				</template>
				<template slot="actions">
					<ActionButton icon="icon-delete" @click="removeDb(missingItem)">
						ファイル情報を削除
					</ActionButton>
				</template>
			</ListItem>
		</ul>

		<ul>
			<ListItem v-for="item of localListItems"
				:key="item.id"
				:bold="true"
				:title="item.filename">
				<template slot="icon">
					<Avatar v-if="item.filetype.search(/image/) == -1 " icon-class="icon-file" :size="48" />
					<Avatar v-else-if="item.href" :url="item.href" :size="48" />
					<Avatar v-else icon-class="icon-picture" :size="48" />
				</template>
				<template slot="subtitle">
					{{ item.updated2 }}  ({{ item.size }} bytes)
					<span v-if="unregisteredItemIds.includes(item.id)">★</span>
				</template>
				<template slot="actions">
					<ActionCheckbox v-if="item.filetype.search(/image/) !== -1 && fileInfo" :checked="item.isEyecatch==1" @change="setEyecatch(item,$event)">
						アイキャッチにする
					</ActionCheckbox>
					<ActionButton v-if="item.filetype.search(/image/) !== -1 && fileInfo" icon="icon-picture" @click="addContent(item)">
						本文に表示する
					</ActionButton>
					<ActionButton icon="icon-delete" @click="removeFile(item)">
						ファイルを削除
					</ActionButton>
				</template>
			</ListItem>
		</ul>
	</div>
</template>
<script>
import ListItem from '@nextcloud/vue/dist/Components/ListItem'
import Avatar from '@nextcloud/vue/dist/Components/Avatar'
import ActionButton from '@nextcloud/vue/dist/Components/ActionButton'
import ActionCheckbox from '@nextcloud/vue/dist/Components/ActionCheckbox'
import Mymodules from '../js/modules'
export default {
	name: 'DirList',
	components: {
		ListItem,
		Avatar,
		ActionButton,
		ActionCheckbox,
	},
	props: {
		items: {
			type: Array,
			default: () => { return [] },
		},
		userId: {
			type: String,
			default: '',
		},
		fileInfo: {
			type: Array,
			default: () => { return [] },
		},
	},
	computed: {
		localListItems: {
			get() {
				return this.items
				// return this.items.map((item) => {
				// const target = this.fileInfo.find((file) => Number(file.id) === Number(item.fileId))
				// //console.info(target)
				// if (target && target.is_eyecatch) {
				// item.isEyecatch = true
				// } else {
				// item.isEyecatch = false
				// }
				// return item
				// })
			},
			set(val) {
				this.$emit('update:items', val)
			},
		},
		missingItems() {
			return this.fileInfo.filter((item) => !item.exist)

		},
		unregisteredItemIds() {
			const registeredIds = this.fileInfo.map((item) => Number(item.id))
			return this.items.filter((item) => !registeredIds.includes(Number(item.fileId)))
		},

	},
	methods: {
		removeFile(file) {
			Mymodules.removeFile(file)
			this.localListItems = this.localListItems.filter((item) => item.fileId !== file.fileId)

		},
		removeDb(file) {
			Mymodules.removeDataOfFile(file.id)
			const tmpList = this.fileInfo.filter((item) => item.id !== file.id)
			this.$emit('update:fileInfo', tmpList)
		},
		setEyecatch(file, e) {
			 file.isEyecatch = e.target.checked
			 const tmpArray = this.localListItems.filter((item) => item.fileId !== file.fileId)
			 tmpArray.push(file)
			 this.localListItems = tmpArray
		},
		addContent(item) {
			const addText = `<div><img src="${item.href}" /></div>`
			this.$emit('addcontent', addText)

		},
	},
}
</script>
<style scoped>
.align-left{
	text-align:start;
}
</style>
