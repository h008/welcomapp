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
		if (!note.id) { note.id = -1 }
		if (!note.category) { note.category = 0 }
		// if (!note.pin_flag) { note.pin_flag = false }
		// if (!note.pub_flag) { note.pub_flag = false }
		if (note.id === -1) {
			return createNote(note)
		} else {
			return updateNote(note)
		}
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

		try {
			await axios.delete(generateUrl(`/apps/welcomapp/notes/${note.id}`))
			return note
		} catch (e) {
			console.error(e)
			showError(t('welcomapp', 'Could not delete'))
		}
	},
	removeAttachedFiles: (note) => {
		if (!note.shareId || !note.uuid) { return false }
			 axios.get(`/ocs/v2.php/apps/files_sharing/api/v1/shares/${note.shareId}`, { headers: { 'OCS-APIRequest': true } }).then((result) => {

			 const dir = result?.data?.ocs?.data[0]?.file_target
			if (dir) {
				const path = `${note.userId}${dir}/${note.uuid}`
				const dirInfo = fetchDirList(path)
				dirInfo.forEach((file) => {
					this.removeFile(file)
				})
				axios.get(generateUrl(`/apps/welcomapp/getfiles/${this.localCurrentNote.uuid}`)).then((result) => {
					if (result.data) {
						result.data.forEach((file) => {
							this.removeDataOfFile(file.id)
						})
					}
				})
			 }
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
		try {
			await axios.delete(generateUrl(`/apps/welcomapp/categories/${category.id}`))
			return category
		} catch (e) {
			console.error(e)
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
		try {
			await axios.delete(generateUrl(`/apps/welcomapp/tags/${tag.id}`))
			return tag
		} catch (e) {
			console.error(e)
			showError(t('welcomapp', 'Could not delete'))
		}
	},
	/**
	 * Update an existing note on the server
	 *
	 * @param {string} path string
	 */
	fetchDirInfoOrCreate: async (path) => {
		return fetchDirList(path).then((result) => {
			if (result.status === 404) {
				console.info('404error')
				return createDir(path).then((crResult) => {
					if (crResult.status === 201) {
						return fetchDirList(path)
					}
					return crResult

				}).catch((e) => {
					console.info(e)
					if (e.response) {
						return e.response
					}
					return e
				})
			} else {
				return result

			}
		})

	},
	/**
	 * Update an existing note on the server
	 *
	 * @param {string} path string
	 */
	fetchDirInfo: async (path) => {
		return fetchDirList(path)

	},
	removeFile(file) {
		axios.delete(file.href).then(() => {
			this.removeDataOfFile(file.fileId)

		})
	},
	removeDataOfFile(fileId) {
		return axios.delete(generateUrl(`/apps/welcomapp/files/${fileId}`))

	},
	autherInfo(userId) {
		if (!userId) { return { displayname: '' } }
		return axios.get(generateUrl(`/apps/welcomapp/getuser/${userId}`)).then((userInfo) => {
			return userInfo.data
		})
	},
	fetchShareInfo(shareId) {
		if (!shareId) { return '' }
			 return axios.get(`/ocs/v2.php/apps/files_sharing/api/v1/shares/${shareId}`, { headers: { 'OCS-APIRequest': true } }).then((result) => {

			 return result?.data?.ocs?.data[0]?.file_target
			 })

	},
	fetchHeader(userId) {
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
							const headerDir = `${userId}${userDir}/headers`
							return this.fetchDirInfo(headerDir).then((dirInfo) => {
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
			return result.data.map((elem) => {
				if (elem.filetype === 'folder') {
					elem.userRef = generateUrl(`/f/${elem.id}`)

				} else {
					elem.userRef = `/remote.php/dav/files/${userId}${userDir}/${elem.fileurl}/${elem.filename}`
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

	fetchNotes(userId, propFilter) {
		const defFilter = { category: 0, offset: 0, limit: 0, pubFlag: true, pinFlag: false }
		const filter = { ...defFilter, ...propFilter }

		const dataP = axios.get(generateUrl('/apps/welcomapp/filter'), { params: filter }).then((result) => {
			let data = result.data
			if (!data || !data.length) {
				return Promise.resolve([])

			}
			 data = data.map((note) => {
				if (note.userId && note.shareId) {
					const userInfoP = this.autherInfo(note.userId)
					const userDirP = this.fetchShareInfo(note.shareId)
					return Promise.all([userInfoP, userDirP]).then(([userInfo, userDir]) => {
						note.userInfo = userInfo
						note.userDir = userDir
				 if (note.content) {
					 const beforeStr = `/${note.userId}/announce`
					 const afterStr = `/${userId}${userDir}`
							note.content = note.content.replace(beforeStr, afterStr)
				 }
						if (userId && userDir && note.uuid) {
							const path = `${userId}${userDir}/${note.uuid}`
							const dirInfo = this.fetchDirInfo(path)
							const fileInfo = this.fetchFileInfo(note.uuid, userId, userDir)
							return Promise.all([fileInfo, dirInfo]).then((array) => {
								const checked = this.compareFileInfo(array, note)
								note.fileInfo = checked.fileInfo
								note.dirInfo = checked.dirInfo
								return note
							})
						} else {
							return Promise.resolve(note)
						}
					})
				} else {
					return Promise.resolve(note)
				}

			})
			return Promise.all(data)
		}).catch((e) => {
			console.error(e)
			showError(t('welcomapp', 'Could not fetch notes'))
			return Promise.resolve([])
		})
			 const totalP = axios.get(generateUrl('/apps/welcomapp/filtercount'), { params: filter }).then((result) => {
			  return result.data
			 }).catch((e) => {
			console.error(e)
			showError(t('welcomapp', 'Could not fetch notes'))
				 return Promise.resolve(0)
			 })
		return Promise.all([totalP, dataP]).then((array) => {
			return { total: array[0], data: array[1] }
		})

	},

}

/**
 * Update an existing note on the server
 *
 * @param {string} xml string
 */
const parseXml = (xml) => {
	const parser = new DOMParser()
	const dom = parser.parseFromString(xml, 'text/xml')
	const response = dom.getElementsByTagName('d:multistatus')[0].getElementsByTagName('d:response')
	const result = []
	response.forEach((element) => {
		const prop = element.getElementsByTagName('d:propstat')[0].getElementsByTagName('d:prop')[0]
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
		let filename = tmpArray.pop()
		if (!filename) {
			filename = tmpArray.pop()
			filetype = 'folder'
		}
		const href2 = generateUrl(`/f/${fileId}`)

		result.push({ href, modified, size, filetype, fileId, hasPreview, filename, href2, updated, updated2, unregist })
	})

	return result
}
/**
 * Update an existing note on the server
 *
 * @param {string} path string
 */
const fetchDirList = async (path) => {
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
		return parseXml(result.data)
	}).catch((e) => {
		if (e.response) {
			console.info(e.response)
			return e.response
		} else {
			return e
		}
	})

}
/**
 * Update an existing note on the server
 *
 * @param {string} path string
 */
const createDir = async (path) => {

	 return axios.request({ url: `/remote.php/dav/files/${path}`, method: 'MKCOL' }).then((result) => {

	}).catch((e) => {
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
	try {
		const response = await axios.post(
			generateUrl('/apps/welcomapp/notes'),
			note
		)
		return response
	} catch (e) {
		console.error(e)
		showError(t('welcomapp', 'Could not create the note'))
	}
}
/**
 * Update an existing note on the server
 *
 * @param {object} note Note object
 */
	 const updateNote = async (note) => {
	try {
		const response = await axios.put(generateUrl(`/apps/welcomapp/notes/${note.id}`), note)
		return response
	} catch (e) {
		console.error(e)
		showError(t('welcomapp', 'Could not update the note'))
	}

}
/**
 * Create a new note by sending the information to the server
 *
 * @param {object} category Category object
 */
	 const createCategory = async (category) => {
	try {
		const response = await axios.post(
			generateUrl('/apps/welcomapp/categories'),
			category
		)
		return response
	} catch (e) {
		console.error(e)
		showError(t('welcomapp', 'Could not create the category'))
	}
}
/**
 * Update an existing category on the server
 *
 * @param {object} category Category object
 */
	 const updateCategory = async (category) => {
	try {
		const response = await axios.put(generateUrl(`/apps/welcomapp/categories/${category.id}`), category)
		return response
	} catch (e) {
		console.error(e)
		showError(t('welcomapp', 'Could not update the category'))
	}
}
/**
 * Create a new note by sending the information to the server
 *
 * @param {object} tag Tag object
 */
	 const createTag = async (tag) => {
	try {
		const response = await axios.post(
			generateUrl('/apps/welcomapp/tags'),
			tag
		)
		return response
	} catch (e) {
		console.error(e)
		showError(t('welcomapp', 'Could not create the tag'))
	}
}
/**
 * Update an existing tag on the server
 *
 * @param {object} tag Tag object
 */
	 const updateTag = async (tag) => {
	try {
		const response = await axios.put(generateUrl(`/apps/welcomapp/tags/${tag.id}`), tag)
		return response
	} catch (e) {
		console.error(e)
		showError(t('welcomapp', 'Could not update the tag'))
	}
	 }
