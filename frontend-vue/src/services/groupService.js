import { request } from './apiService'

export function getGroups() {
  return request('/groups')
}

export function getGroup(groupId) {
  return request(`/groups/${groupId}`)
}

export function createGroup(groupData) {
  return request('/groups', {
    method: 'POST',
    body: groupData,
  })
}

export function updateGroup(groupId, groupData) {
  return request(`/groups/${groupId}`, {
    method: 'PUT',
    body: groupData,
  })
}

export function deleteGroup(groupId) {
  return request(`/groups/${groupId}`, {
    method: 'DELETE',
  })
}

export function addMember(groupId, email, role = 'member') {
  return request(`/groups/${groupId}/members`, {
    method: 'POST',
    body: {
      email,
      role: 'member',
    },
  })
}

export function removeMember(groupId, userId) {
  return request(`/groups/${groupId}/members/${userId}`, {
    method: 'DELETE',
  })
}