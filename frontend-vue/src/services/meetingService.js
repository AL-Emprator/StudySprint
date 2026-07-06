import { request } from './apiService';

export async function getMeetings(groupId) {
  return await request(`/groups/${groupId}/meetings`);
}

export async function getMeeting(meetingId) {
  return await request(`/meetings/${meetingId}`);
}

export async function createMeeting(groupId, meetingData) {
  return await request(`/groups/${groupId}/meetings`, {
    method: 'POST',
    body: meetingData,
  });
}

export async function updateMeeting(meetingId, meetingData) {
  return await request(`/meetings/${meetingId}`, {
    method: 'PUT',
    body: meetingData,
  });
}

export async function deleteMeeting(meetingId) {
  return await request(`/meetings/${meetingId}`, {
    method: 'DELETE',
  });
}
