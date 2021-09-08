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
		console.info('deleteNote')
		 removeAttachedFiles(note).then(() => {
			 axios.delete(generateUrl(`/apps/welcomapp/notes/${note.id}`))
		}).catch((e) => {
			console.error(e)
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
		return checkDirExist(path).then((exist) => {
			if (exist) {
				return fetchDirList(path)

			} else {
				// return Promise.resolve([])
				return fetchDirList(path)
			}
		})

	},
	removeFile(file) {
		return axios.delete(file.href).then(() => {
			return this.removeDataOfFile(file.fileId)

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
			console.info('fetchShareInfo')
				 console.info(result.data.ocs.data[0])
			 // return result?.data?.ocs?.data[0]?.file_target
			 return result?.data?.ocs?.data[0]?.path
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
							const headerDir = `${userId}${userDir}`
							console.info('headerDir')
							console.info(headerDir)
							return this.fetchDirInfo(headerDir).then((dirInfo) => {
								console.info('dirInfo')
								console.info(dirInfo)
								const regex = /image/
								const headerDirInfo = dirInfo.filter((element) => regex.test(element.filetype)).map((file) => file.href)
								console.info('headerDirInfo')
								console.info(headerDirInfo)
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
			if (!result || !result.data || !result.data.length) { return [] }
			return result.data.map((elem) => {
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
	fetchNotes(user, propFilter) {
		console.info('debug1')
		const defFilter = { category: 0, offset: 0, limit: 0, pubFlag: true, pinFlag: false }
		const filter = { ...defFilter, ...propFilter }
		const userId = user.id
		const userGroups = user.groups

		const dataP = axios.get(generateUrl('/apps/welcomapp/filter'), {
			params: filter,
			// paramsSerializer: () => {
			// return transformRequestOptions(filter)
			// },
		}).then((result) => {
			let data = result.data
			if (!data || !data.length) {
				return Promise.resolve([])

			}
			// TODO
			 data = data.map((note) => {
				 if (note.userId && note.shareInfo) {
					 const shareInfos = JSON.parse(note.shareInfo)
					const shareId = shareInfos.filter((shareInfo) => userGroups.includes(shareInfo.gid)).map((share) => share.shareId)[0]
					 if (!shareId) { return {} }

					const userInfoP = this.autherInfo(note.userId)
					 console.info('userInfo')
					console.info(userInfoP)
					const userDirP = this.fetchShareInfo(shareId)
					 console.info('userDir')
					 console.info(userDirP)
					return Promise.all([userInfoP, userDirP]).then(([userInfo, userDir]) => {
						note.userInfo = userInfo
						note.userDir = userDir
						console.info('promise1')
						console.info(note)
				 if (note.content) {
					 // TODO
							const targetStr = note.content
							const beforeStr = `/${note.userId}/.announce_${note.uuid}`
							const afterStr = `/${userId}${userDir}`
							const reg = new RegExp(beforeStr, 'g')
							note.content = targetStr.replace(reg, afterStr)
				 }
						if (userId && userDir && note.uuid) {
							const path = `${userId}${userDir}`
							console.info('path')
							console.info(path)
							const dirInfo = this.fetchDirInfo(path)
							console.info(dirInfo)
							const fileInfo = this.fetchFileInfo(note.uuid, userId, userDir)
							console.info(fileInfo)
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
	checkDir(path) {
		return checkDirExist(path)
	},
	fetchShareDir(shareInfoStr, user) {
		console.info('here')
		if (shareInfoStr) {
			const shareInfo = JSON.parse(shareInfoStr)
			if (!shareInfo.length) {
				console.info(shareInfo)

			}
			if (!user.groups?.length) { return '' }
			const shareId = shareInfo.filter((info) => user.groups.includes(info.gid)).map((elm) => elm.shareId)[0]

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
// console.info(key)
// console.info(typeof params[key])
// console.info(params[key])
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
	console.info('remove')
	console.info(note)
	if (!note.shareInfo || !note.uuid) { return false }
	console.info(note.shareInfo)

	const shareInfo = JSON.parse(note.shareInfo)
		   const shareId = shareInfo[0].shareId
	console.info(shareId)
	return axios.get(`/ocs/v2.php/apps/files_sharing/api/v1/shares/${shareId}`, { headers: { 'OCS-APIRequest': true } }).then((result) => {

			 const dir = result?.data?.ocs?.data[0]?.file_target
		if (dir) {
			const promises = []
			const path = `${note.userId}${dir}`
			return axios.delete(`/remote.php/dav/files/${path}`).then(() => {

				return axios.get(generateUrl(`/apps/welcomapp/getfiles/${note.uuid}`)).then((result) => {
					if (result.data) {
						result.data.forEach((file) => {
							promises.push(axios.delete(generateUrl(`/apps/welcomapp/files/${file.id}`)))
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
const checkDirExist = async (path) => {
	const href = path.split('/')
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
		const parser = new DOMParser()
		const dom = parser.parseFromString(result.data, 'text/xml')
		let response = dom.getElementsByTagName('d:multistatus')[0]?.getElementsByTagName('d:response')[0]?.getElementsByTagName('d:href')[0]?.textContent
		if (!response) { return false }
		response = response.replace(/^\/remote.php\/dav\/files\//, '').replace(/\/$/, '')
		return (response === path)
	}).catch((e) => {
		console.info('searchEror')
		console.info(e)
		return false
	})

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
