export const PROJECT_STATUS = {
  draft: { label: 'Brouillon', color: 'grey' },
  published: { label: 'Publié', color: 'success' },
  archived: { label: 'Archivé', color: 'warning' },
}

export const TEMPLATE_STATUS = {
  draft: { label: 'Brouillon', color: 'grey' },
  published: { label: 'Publié', color: 'success' },
  archived: { label: 'Archivé', color: 'warning' },
}

export function projectStatusLabel(status) {
  return PROJECT_STATUS[status]?.label ?? status
}

export function projectStatusColor(status) {
  return PROJECT_STATUS[status]?.color ?? 'default'
}

export function templateStatusLabel(status) {
  return TEMPLATE_STATUS[status]?.label ?? status
}

export function templateStatusColor(status) {
  return TEMPLATE_STATUS[status]?.color ?? 'default'
}