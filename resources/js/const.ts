export const roles = [
  {
    label: '管理員',
    value: 'admin',
    description: '管理員是房間內權限最高的角色，擁有包含邀請/請出成員、修改房間設定、上傳檔案等權限。',
    color: 'blue',
  },
  {
    label: '上傳影片',
    value: 'uploader',
    description: '可以上傳影片的成員角色。',
    color: 'yellow',
  },
  {
    label: '成員',
    value: 'user',
    description: '成員是房間內權限最低的角色，可以同步觀看影片、新增/刪除播放項目，但無法上傳檔案、邀請成員。',
    color: 'green',
  },
] as {
  label: string
  value: string
  description: string
  color: 'red' | 'yellow' | 'green' | 'blue'
}[]
