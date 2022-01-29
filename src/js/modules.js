import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { showError } from '@nextcloud/dialogs'
import dayjs from 'dayjs'
dayjs.extend(require('dayjs/plugin/utc'))
dayjs.extend(require('dayjs/plugin/timezone'))
dayjs.tz.setDefault('Asia/Tokyo')
export default {
	logtest: () => {
		alert('test')
	},
	/**
	 * Action tiggered when clicking the save button
	 * create a new note or save
	 *
	 * @param {object} note Object
	 */
	saveNote: (note) => {
		if (!note.readusers) {
			note.readusers = ''
		}
		const noteObj = {
			id: note.id,
			title: note.title,
			content: note.content,
			category: note.category,
			pinFlag: note.pinFlag,
			pubFlag: note.pubFlag,
			tags: note.tags,
			uuid: note.uuid,
			shareInfo: note.shareInfo,
			readusers: note.readusers,
			updateflg: 1,

		}
		if (!note.id) { noteObj.id = -1 }
		if (!note.category) { noteObj.category = 0 }
		if (note.pubFlag && (!note.shareInfo || !note.shareInfo.match(/shareId/))) {
			noteObj.pubFlag = 0
		} else { noteObj.pubFlag = Number(note.pubFlag) }
		if (!note.pinFlag) { noteObj.pinFlag = 0 } else { noteObj.pinFlag = Number(note.pinFlag) }
		// if (!note.pin_flag) { note.pin_flag = false }
		// if (!note.pub_flag) { note.pub_flag = false }
		if (noteObj.id === -1) {
			return createNote(noteObj)
		} else {
			return updateNote(noteObj)
		}
	},
	updateRead: (note) => {
		if (!note.id) { return }
		if (!note.readusers) {
			note.readusers = ''
		}
		const noteObj = {
			id: note.id,
			title: note.title,
			content: note.content,
			category: note.category,
			pinFlag: note.pinFlag,
			pubFlag: note.pubFlag,
			tags: note.tags,
			uuid: note.uuid,
			shareInfo: note.shareInfo,
			readusers: note.readusers,
			updateflg: 0,

		}
		return updateNote(noteObj)

	},
	/**
	 * Abort creating a new note
	 */
	cancelNewNote: () => {
		this.notes.splice(
			this.notes.findIndex((note) => note.id === -1),
			1
		)
		this.currentNoteId = null
		this.currentNote = null
		this.content = null
	},
	/**
	 * Delete a note, remove it from the frontend and show a hint
	 *
	 * @param {object} note Note object
	 */
	deleteNote: async (note) => {
		 removeAttachedFiles(note).then(() => {
			 if (note.id > 0) {
			 axios.delete(generateUrl(`/apps/welcomapp/notes/${note.id}`))
			 }
		}).catch((e) => {
			showError(t('welcomapp', 'Could not delete'))
		})
	},
	/**
	 * Action tiggered when clicking the save button
	 * create a new note or save
	 *
	 * @param {object} category Object
	 */
	saveCategory: (category) => {
		if (!category.id) { category.id = -1 }
		if (category.id === -1) {
			return createCategory(category)
		} else {
			return updateCategory(category)
		}
	},
	/**
	 * Delete a category, remove it from the frontend and show a hint
	 *
	 * @param {object} category Category object
	 */
	deleteCategory: async (category) => {
		if (!category || !category.id) { return }
		try {
			await axios.delete(generateUrl(`/apps/welcomapp/categories/${category.id}`))
			return category
		} catch (e) {
			showError(t('welcomapp', 'Could not delete'))
		}
	},
	/**
	 * Action tiggered when clicking the save button
	 * create a new note or save
	 *
	 * @param {object} tag Object
	 */
	saveTag: (tag) => {
		if (!tag.id) { tag.id = -1 }
		if (tag.id === -1) {
			return createTag(tag)
		} else {
			return updateTag(tag)
		}
	},
	/**
	 * Delete a tag, remove it from the frontend and show a hint
	 *
	 * @param {object} tag Tag object
	 */
	deleteTag: async (tag) => {
		if (!tag || !tag.id) { return }
		try {
			await axios.delete(generateUrl(`/apps/welcomapp/tags/${tag.id}`))
			return tag
		} catch (e) {
			showError(t('welcomapp', 'Could not delete'))
		}
	},
	/**
	 * Update an existing note on the server
	 *
	 * @param {string} path string
	 */
	fetchDirInfoOrCreate: async (path) => {
		if (!path) { return }
		return checkDirExist(path).then((exist) => {

			if (exist) {
				return fetchDirList(path).catch((e) => { return e })
			} else {
				return createDir(path).then((crResult) => {
					if (crResult.status === 201) {
						return fetchDirList(path).catch((e) => { return e })
					} else {
						return crResult
					}
				}).catch((e) => { return e })
			}
		}).catch((e) => { return e })
	},
	/**
	 * Update an existing note on the server
	 *
	 * @param {string} path string
	 */
	fetchDirInfo: async (path) => {
		if (!path) { return }
		return checkDirExist(path).then((exist) => {
			if (exist) {
				return fetchDirList(path)

			} else {
				// return Promise.resolve([])
				return fetchDirList(path)
			}
		})

	},
	fetchSharedComments(shareId) {
		return fetchComments(shareId)

	},
	putSharedComments(userId, shareId, comment) {
		return putComments(userId, shareId, comment)
	},
	removeSharedComments(shareId, commentId) {
		return removeComments(shareId, commentId)

	},
	removeFile(file) {
		if (!file || !file.href) { return Promise.resolve() }
		return axios.delete(file.href).then(() => {
			if (!file.fileId) { return Promise.resolve() }
			return this.removeDataOfFile(file.fileId)

		})
	},
	removeDataOfFile(fileId) {
		if (!fileId) { return Promise.resolve() }
		return axios.delete(generateUrl(`/apps/welcomapp/files/${fileId}`))

	},
	autherInfo(userId) {
		if (!userId) { return { displayname: '' } }
		return axios.get(generateUrl(`/apps/welcomapp/getuser/${userId}`)).then((userInfo) => {
			return userInfo.data
		})
	},
	shareDirToGroup(path, gid) {
		if (!path || !gid) {
			return
		}
		const data = { path, shareType: 1, shareWith: gid, publicUpload: 'false', permissions: 1 }
		return axios.post('/ocs/v2.php/apps/files_sharing/api/v1/shares', data, { headers: { 'OCS-APIRequest': true } }).then((result) => {
			const shareId = result?.data?.ocs?.data?.id
			return shareId
		})

	},
	fetchShareInfo(shareId) {
		if (!shareId) { return '' }
			 return axios.get(`/ocs/v2.php/apps/files_sharing/api/v1/shares/${shareId}`, { headers: { 'OCS-APIRequest': true } }).then((result) => {
			 return result?.data?.ocs?.data[0]?.path
			 }).catch(() => { return '' })

	},
	fetchHeader(userId) {
		if (!userId) { return Promise.resolve() }
		return axios.get(generateUrl('/apps/welcomapp/getconfig/header')).then((result) => {
			if (result.data && result.data.length) {

				const tmpData = result.data[0]
				if (tmpData.value) {
					let s = tmpData.value
					// preserve newlines, etc - use valid JSON
					s = s.replace(/\\n/g, '\\n')
						.replace(/\\'/g, "\\'")
						.replace(/\\"/g, '\\"')
						.replace(/\\&/g, '\\&')
						.replace(/\\r/g, '\\r')
						.replace(/\\t/g, '\\t')
						.replace(/\\b/g, '\\b')
						.replace(/\\f/g, '\\f')

					// remove non-printable and other non-valid JSON chars
					// s = s.replace(/[\u0000-\u0019]+/g, '')
					const value = JSON.parse(s)
					tmpData.value = value
					if (value.shareId) {
						return this.fetchShareInfo(value.shareId).then((userDir) => {
							if (!userDir) { return Promise.resolve(tmpData) }
							const headerDir = `${userId}${userDir}`
							return this.fetchDirInfo(headerDir).then((dirInfo) => {
								if (!dirInfo || !dirInfo.length) { return tmpData }
								const regex = /image/
								const headerDirInfo = dirInfo.filter((element) => regex.test(element.filetype)).map((file) => file.href)
								tmpData.images = headerDirInfo
								return tmpData
							})

						})

					} else {
						return tmpData
					}
				} else {
					return tmpData

				}
			} else {
				return Promise.resolve({})
			}
		})

	},
		 fetchFileInfo(uuid, userId, userDir) {

			 if (!uuid) { return Promise.resolve([]) }
		return axios.get(generateUrl(`/apps/welcomapp/getfiles/${uuid}`)).then((result) => {
			if (!result || !result.data || !result.data.length) {
				 return []
			}
			return result.data.map((elem) => {
				if ((elem.isEyecatch && (elem.isEyecatch === '0' || elem.isEyecatch === 'false' || elem.isEyecatch === 0)) || !elem.isEyecatch) { elem.isEyecatch = 0 }
				if ((elem.hasPreview && (elem.hasPreview === '0' || elem.hasPreview === 'false' || elem.hasPreview === 0)) || !elem.hasPreview) { elem.hasPreview = false }
				if (elem.filetype === 'folder') {
					elem.userRef = generateUrl(`/f/${elem.id}`)

				} else {
					elem.userRef = `/remote.php/dav/files/${userId}${userDir}/${elem.filename}`
				}
				return elem
			})

		})

	},
	compareFileInfo(fileListArray) {
		let realFiles = []
		let dbFiles = []
		let fileInfo = []
		let dirInfo = []
		if (fileListArray && fileListArray.length) {
			if (fileListArray[1] && fileListArray[1].length) {
			 realFiles = fileListArray[1].map((rfile) => Number(rfile.fileId))

			}
			if (fileListArray[0] && fileListArray[0].length) {
			 dbFiles = fileListArray[0].map((dfile) => Number(dfile.id))
			 fileInfo = fileListArray[0].map((dFileInfo) => {
					dFileInfo.exist = realFiles.includes(Number(dFileInfo.id))
					return dFileInfo
				})

			}
			if (dbFiles && dbFiles.length) {
				dirInfo = fileListArray[1].map((rFileInfo) => {
					rFileInfo.registered = dbFiles.includes(Number(rFileInfo.fileId))
					const target = dbFiles.find((file) => Number(file.id) === Number(rFileInfo.fileId))
					if (target) {
						rFileInfo.isEyecatch = target.isEyecatch
					}

					return rFileInfo
				})

			}

		}
		return { fileInfo, dirInfo }
	},
	// TODO
	async fetchNotes(user, propFilter) {
		const defFilter = { category: 0, offset: 0, limit: 10, pubFlag: 1, pinFlag: 0, tags: 'all', unread: 0 }
		const filter = { ...defFilter, ...propFilter }
		const userId = user.id
		 // const userGroups = user.groups

		const total = await this.countTotalNote(filter)

		if (!total) {
			return { total, data: [] }
		}

		const data = await this.fetchFilteredNotes(filter)
		console.info(data)
		const addedData = data.map((note) => {
			if (note.readusers) {
				const readusersArray = note.readusers.split(',')
				note.isRead = readusersArray.includes(userId)

			} else {
				note.readusers = ''
				note.isRead = false
			}
			return note

		})
		console.info(addedData)

		const result = addedData.filter((el) => el.id)
		console.info(result)
		return { total, data: result }

	},
	async addShareInfoNote(userId, note, userGroups) {
		if (!note.userId || !note.shareInfo) {
			return note
		}

		const shareInfos = JSON.parse(note.shareInfo)
		const shareId = shareInfos.filter(
			(shareInfo) => {
				return (userGroups.includes(shareInfo.gid) || note.userId === userId)
			}
		).map((share) => share.shareId)[0]
		let userDirP
		if (!shareId) {
			if (note.userId !== userId) {
				return {}
			}
			note.pubFlag = 0
			userDirP = Promise.resolve(`.announce_${note.uuid}`)
		} else {
			userDirP = this.fetchShareInfo(shareId)
		}
		const userInfoP = this.autherInfo(note.userId)
		const allGroupsP = axios.get(generateUrl('/apps/welcomapp/getallgroups')).then((result) => {
			if (result && result.data) {
				return result.data.filter((group) => group.gid !== 'admin')
			} else {
				return []
			}
		})

		return await this.promiseAllThenReturnNote(note, userId, userInfoP, userDirP, allGroupsP, shareInfos)
	},
	async fetchFilteredNotes(filter) {

		return axios.get(generateUrl('/apps/welcomapp/filter'), {
			params: filter,
		}).then((result) => {
			const data = result.data
			console.info(result)

			if (!data || !data.length) {
				return []
			}
			return data
		}).catch((e) => {
			showError(t('welcomapp', 'Could not fetch notes'))
			return []
		})
	},

	async countTotalNote(filter) {
		return await axios.get(generateUrl('/apps/welcomapp/filtercount'), { params: filter })
			.then((result) => {
				return result.data
			}).catch((e) => {
				showError(t('welcomapp', 'Could not fetch notes'))
				return Promise.resolve(0)
			})
	},

	async promiseAllThenReturnNote(note, userId, userInfoP, userDirP, allGroupsP, shareInfos) {

		return Promise.all([userInfoP, userDirP, allGroupsP]).then(
			async ([userInfo, userDir, allGroups]) => {
				note.userInfo = userInfo
				note.userDir = userDir
				if (shareInfos) {
					note.shareGroups = shareInfos.map((share) => allGroups.find((group) => group.id === share.gid))
				}
				note = this.replaceContent(note, userId, userDir)

				return await this.returnNoteWithCheckedFileInfo(note, userId, userDir)
			})
	},
	replaceContent(note, userId, userDir) {
		console.info('replace content')
		if (note.content && note.userId !== userId) {
			// TODO

			const targetStr = note.content
			const beforeStr = `/${note.userId}/.announce_${note.uuid}`
			const afterStr = `/${userId}${userDir}`
			const reg = new RegExp(beforeStr, 'g')
			note.content = targetStr.replace(reg, afterStr)
		}
		return note
	},
	async returnNoteWithCheckedFileInfo(note, userId, userDir) {
		if (userId && userDir && note.uuid) {
			const path = `${userId}${userDir}`
			const dirInfo = this.fetchDirInfo(path)
			const fileInfo = this.fetchFileInfo(note.uuid, userId, userDir)
			return Promise.all([fileInfo, dirInfo]).then((array) => {
				note.baseFileId = array[1][0].fileId
				const checked = this.compareFileInfo(array)
				note.fileInfo = checked.fileInfo
				note.dirInfo = checked.dirInfo
				return note
			})
		} else {
			return Promise.resolve(note)
		}

	},
	checkDir(path) {
		if (!path) { return false }
		return checkDirExist(path)
	},
	fetchShareDir(shareInfoStr, user) {
		if (shareInfoStr) {
			const shareInfo = JSON.parse(shareInfoStr)
			if (!shareInfo.length) {
				return ''

			}
			if (!user.groups?.length) { return '' }
			const shareId = shareInfo.filter((info) => user.groups.includes(info.gid)).map((elm) => elm.shareId)[0]
			if (!shareId) { return '' }

			 return axios.get(`/ocs/v2.php/apps/files_sharing/api/v1/shares/${shareId}`, { headers: { 'OCS-APIRequest': true } }).then((result) => {

			 return result?.data?.ocs?.data[0]?.file_target
			 })
			 }

	},

}

// const transformRequestOptions = (params) => {
// let options = ''
// for (const key in params) {
// if (typeof params[key] !== 'object') {
// options += `${key}=${params[key]}&`
// } else if (typeof params[key] === 'object') {
// const value = JSON.stringify(params[key])
// options += `${key}=${value}&`
// } else {
// }
// }
// return options ? options.slice(0, -1) : options

// }
/**
 * Delete a note, remove it from the frontend and show a hint
 *
 * @param {object} note Note object
 */

const removeAttachedFiles = (note) => {
	if (!note || !note.shareInfo || !note.uuid || !note.userId) { return false }

	const shareInfo = JSON.parse(note.shareInfo)
	if (!shareInfo || !shareInfo.length) { return Promise.resolve() }
		   const shareId = shareInfo[0].shareId
	if (!shareId) { return Promise.resolve() }
	return axios.get(`/ocs/v2.php/apps/files_sharing/api/v1/shares/${shareId}`, { headers: { 'OCS-APIRequest': true } }).then((result) => {

			 const dir = result?.data?.ocs?.data[0]?.file_target
		if (dir) {
			const promises = []
			const path = `${note.userId}${dir}`
			if (!path) { return Promise.resolve() }
			return axios.delete(`/remote.php/dav/files/${path}`).then(() => {

				return axios.get(generateUrl(`/apps/welcomapp/getfiles/${note.uuid}`)).then((result) => {
					if (result.data && result.data.length) {
						result.data.forEach((file) => {
							if (file && file.id) {
								promises.push(axios.delete(generateUrl(`/apps/welcomapp/files/${file.id}`)))
							}
						})
						return Promise.all(promises)
					} else { return Promise.resolve() }
				})

			})

			 } else {
			return Promise.resolve()
		}

	})

}

/**
 * Update an existing note on the server
 *
 * @param {string} xml string
 */
const parseCommentXml = (xml) => {
	if (!xml) {
		return []
	}
	const parser = new DOMParser()
	const dom = parser.parseFromString(xml, 'text/xml')
	const response = dom.getElementsByTagName('d:multistatus')[0]?.getElementsByTagName('d:response')
	if (!response || !response.length) {
		 return []
	}
	const result = []

	response.forEach((element) => {
		const prop = element.getElementsByTagName('d:propstat')[0]?.getElementsByTagName('d:prop')[0]
		const id = prop.getElementsByTagName('oc:id')[0]?.textContent
		const message = prop.getElementsByTagName('oc:message')[0]?.textContent
		// const verb = prop.getElementsByTagName('oc:verb')[0]?.textContent
		const actorId = prop.getElementsByTagName('oc:actorId')[0]?.textContent
		const gmtDateTime = prop.getElementsByTagName('oc:creationDateTime')[0]?.textContent

		const creationDateTime = gmtDateTime ? dayjs(gmtDateTime).format('YYYY-MM-DD HH:mm:ss') : ''
		const actorDisplayName = prop.getElementsByTagName('oc:actorDisplayName')[0]?.textContent
		if (message) {
			result.push({ id, message, actorId, creationDateTime, actorDisplayName })
		}

	})
	result.sort((a, b) => {
		if (Number(a.id) < Number(b.id)) { return -1 }
		if (Number(a.id) > Number(b.id)) { return 1 }
		return 0

	})
	return result
}
/**
 * Update an existing note on the server
 *
 * @param {string} xml string
 */
const parseXml = (xml) => {
	if (!xml) { return [] }
	const parser = new DOMParser()
	const dom = parser.parseFromString(xml, 'text/xml')
	const response = dom.getElementsByTagName('d:multistatus')[0]?.getElementsByTagName('d:response')
	if (!response || !response.length) { return [] }
	const result = []
	response.forEach((element) => {
		const prop = element.getElementsByTagName('d:propstat')[0]?.getElementsByTagName('d:prop')[0]
		const href = element.getElementsByTagName('d:href')[0]?.textContent
		const modified = prop.getElementsByTagName('d:getlastmodified')[0]?.textContent
		const updated = dayjs.tz(modified, 'Asia/Tokyo').format('YYYY-MM-DD HH:mm:ss')
		const updated2 = dayjs.utc(modified).local().format('YYYY-MM-DD HH:mm:ss')
		const size = prop.getElementsByTagName('d:getcontentlength')[0]?.textContent || '0'
		let filetype = prop.getElementsByTagName('d:getcontenttype')[0]?.textContent
		const fileId = prop.getElementsByTagName('oc:fileid')[0]?.textContent
		let unregist = false
		if (fileId) {
			axios.get(generateUrl(`/apps/welcomapp/files/${fileId}`)).then((result) => {
				if (result.status === 204) {
					unregist = true

				}
			})
		}
		const hasPreview = prop.getElementsByTagName('nc:has-preview')[0]?.textContent
		const tmpArray = decodeURI(href).split('/')
		let filename = ''
		if (tmpArray && tmpArray.length) {

			filename = tmpArray.pop()
			if (!filename) {
				filename = tmpArray.pop()
				filetype = 'folder'
			}
		}
		const href2 = generateUrl(`/f/${fileId}`)

		result.push({ href, modified, size, filetype, fileId, hasPreview, filename, href2, updated, updated2, unregist })
	})

	return result
}
const checkDirExist = async (path) => {
	if (!path) { return false }
	const href = path.split('/')
	if (!href && !href.length) { return false }
	const displayName = href.pop()
	const hrefStr = href.join('/')
	const testData = `<?xml version="1.0" encoding="UTF-8"?>
 <d:searchrequest xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns">
     <d:basicsearch>
         <d:select>
             <d:prop>
                 <oc:fileid/>
                 <d:getetag/>
             </d:prop>
         </d:select>
         <d:from>
             <d:scope>
                 <d:href>/files/${hrefStr}</d:href>
                 <d:depth>infinity</d:depth>
             </d:scope>
         </d:from>
         <d:where>
             <d:eq>
                 <d:prop>
                     <d:displayname/>
                 </d:prop>
                 <d:literal>${displayName}</d:literal>
             </d:eq>
         </d:where>
         <d:orderby/>
    </d:basicsearch>
</d:searchrequest>`

	return axios.request({ url: '/remote.php/dav/', data: testData, method: 'SEARCH', headers: { 'content-Type': 'text/xml' } }).then((result) => {
		if (!result || !result.data) { return false }
		const parser = new DOMParser()
		const dom = parser.parseFromString(result.data, 'text/xml')
		let response = dom.getElementsByTagName('d:multistatus')[0]?.getElementsByTagName('d:response')[0]?.getElementsByTagName('d:href')[0]?.textContent
		if (!response) { return false }
		response = response.replace(/^\/remote.php\/dav\/files\//, '').replace(/\/$/, '')
		return (response === path)
	}).catch((e) => {
		return false
	})

}
/**
 * Update an existing note on the server
 *
 * @param {string} path string
 */
const fetchDirList = async (path) => {
	if (!path) { return }
	const data = `<?xml version="1.0"?>
<d:propfind  xmlns:d="DAV:" xmlns:oc="http://owncloud.org/ns" xmlns:nc="http://nextcloud.org/ns">
  <d:prop>
        <d:getlastmodified />
        <d:getcontenttype />
        <oc:fileid />
        <d:getcontentlength />
        <nc:has-preview />
  </d:prop>
</d:propfind>`

	 return axios.request({ url: `/remote.php/dav/files/${path}`, data, method: 'PROPFIND' }).then((result) => {
		 if (!result || !result.data) { return }
		return parseXml(result.data)
	}).catch((e) => {
		if (e.response) {
			return e.response
		} else {
			return e
		}
	})

}

/**
 * Update an existing note on the server
 *
 * @param {string} shareId string
 */
const fetchComments = async (shareId) => {
	if (!shareId) {
		return
	}
	const data = `<?xml version="1.0" encoding="utf-8" ?>
<D:propfind xmlns:D="DAV:" xmlns:oc="http://owncloud.org/ns" >
	<D:prop >
		<oc:id />
		<oc:message />
		<oc:verb />
		<oc:actorId />
		<oc:creationDateTime />
		<oc:actorDisplayName />
		<oc:isUnread />
	</D:prop>
</D:propfind>`
	return axios.request({ url: `/remote.php/dav/comments/files/${shareId}`, data, method: 'PROPFIND' }).then((result) => {
		if (!result || !result.data) {
			return
		}
		return parseCommentXml(result.data)
	}).catch((e) => {
		if (e.response) {
			return e.response
		} else {
			return e
		}
	})
}
/**
 * Update an existing note on the server
 *
 * @param {string} userId string
 * @param {string} fileId string
 * @param {string} message string
 */
const putComments = async (userId, fileId, message) => {
	if (!fileId) {
		return
	}
	return await axios.post(`/remote.php/dav/comments/files/${fileId}`, {
		actorDisplayName: 'test',
		actorId: userId,
		actorType: 'users',
		creationDateTime: (new Date()).toUTCString(),
		message,
		objectType: 'files',
		verb: 'comment',
	})
}
/**
 *
 * @param {string} fileId string
 * @param {string} commentId string
 */
const removeComments = async (fileId, commentId) => {
	if (!fileId || !commentId) {
		return
	}
	return await axios.request({ url: `/remote.php/dav/comments/files/${fileId}/${commentId}`, method: 'DELETE' }).then(() => { return true }).catch(() => { return false })
}
/**
 * Update an existing note on the server
 *
 * @param {string} path string
 */
const createDir = async (path) => {
	if (!path) { return }

	 return axios.request({ url: `/remote.php/dav/files/${path}`, method: 'MKCOL' }).catch((e) => {
		if (e.response) {
			return e.response
		} else {
			return e
		}
	})

}
/**
 * Create a new note by sending the information to the server
 *
 * @param {object} note Note object
 */
	 const createNote = async (note) => {
		 if (!note) { return }
	try {
		const response = await axios.post(
			generateUrl('/apps/welcomapp/notes'),
			note
		)
		return response
	} catch (e) {
		showError(t('welcomapp', 'Could not create the note'))
	}
}
/**
 * Update an existing note on the server
 *
 * @param {object} note Note object
 */
	 const updateNote = async (note) => {
		 if (!note || !note.id) { return }
	try {
		const response = await axios.put(generateUrl(`/apps/welcomapp/notes/${note.id}`), note)
		return response
	} catch (e) {
		showError(t('welcomapp', 'Could not update the note'))
	}

}
/**
 * Create a new note by sending the information to the server
 *
 * @param {object} category Category object
 */
	 const createCategory = async (category) => {
		 if (!category) { return }
	try {
		const response = await axios.post(
			generateUrl('/apps/welcomapp/categories'),
			category
		)
		return response
	} catch (e) {
		showError(t('welcomapp', 'Could not create the category'))
	}
}
/**
 * Update an existing category on the server
 *
 * @param {object} category Category object
 */
	 const updateCategory = async (category) => {
		 if (!category || !category.id) { return }
	try {
		const response = await axios.put(generateUrl(`/apps/welcomapp/categories/${category.id}`), category)
		return response
	} catch (e) {
		showError(t('welcomapp', 'Could not update the category'))
	}
}
/**
 * Create a new note by sending the information to the server
 *
 * @param {object} tag Tag object
 */
	 const createTag = async (tag) => {
		 if (!tag) { return }
	try {
		const response = await axios.post(
			generateUrl('/apps/welcomapp/tags'),
			tag
		)
		return response
	} catch (e) {
		showError(t('welcomapp', 'Could not create the tag'))
	}
}
/**
 * Update an existing tag on the server
 *
 * @param {object} tag Tag object
 */
	 const updateTag = async (tag) => {
		 if (!tag || !tag.id) { return }
	try {
		const response = await axios.put(generateUrl(`/apps/welcomapp/tags/${tag.id}`), tag)
		return response
	} catch (e) {
		showError(t('welcomapp', 'Could not update the tag'))
	}
	 }
