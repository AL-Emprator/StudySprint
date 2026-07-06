import { request } from './apiService'

export function getTasks(groupId) {
  return request(`/groups/${groupId}/tasks`)
}

export function getTask(taskId) {
  return request(`/tasks/${taskId}`)
}

export function createTask(groupId, taskData) {
  return request(`/groups/${groupId}/tasks`, {
    method: 'POST',
    body: taskData,
  })
}

export function updateTask(taskId, taskData) {
  return request(`/tasks/${taskId}`, {
    method: 'PUT',
    body: taskData,
  })
}

export function updateTaskStatus(taskId, status) {
  return request(`/tasks/${taskId}/status`, {
    method: 'PATCH',
    body: {
      status,
    },
  })
}

export function deleteTask(taskId) {
  return request(`/tasks/${taskId}`, {
    method: 'DELETE',
  })
}