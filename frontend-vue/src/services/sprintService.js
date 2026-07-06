import { request } from './apiService';

export async function getSprints(groupId) {
  return await request(`/groups/${groupId}/sprints`);
}

export async function getSprint(sprintId) {
  return await request(`/sprints/${sprintId}`);
}

export async function createSprint(groupId, sprintData) {
  return await request(`/groups/${groupId}/sprints`, {
    method: 'POST',
    body: sprintData,
  });
}

export async function updateSprint(sprintId, sprintData) {
  return await request(`/sprints/${sprintId}`, {
    method: 'PUT',
    body: sprintData,
  });
}

export async function deleteSprint(sprintId) {
  return await request(`/sprints/${sprintId}`, {
    method: 'DELETE',
  });
}
