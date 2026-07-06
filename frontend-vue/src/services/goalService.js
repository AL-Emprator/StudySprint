import { request } from './apiService';

export async function getGoals(groupId) {
  return await request(`/groups/${groupId}/goals`);
}

export function getGoal(goalId) {
  return request(`/goals/${goalId}`)
}

export async function createGoal(groupId, goalData) {
  return await request(`/groups/${groupId}/goals`, {
    method: 'POST',
    body: goalData,
  });
}

export async function updateGoal(goalId, goalData) {
  return await request(`/goals/${goalId}`, {
    method: 'PUT',
    body: goalData,
  });
}

export async function deleteGoal(goalId) {
  return await request(`/goals/${goalId}`, {
    method: 'DELETE',
  });
}
