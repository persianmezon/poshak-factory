export function calculateTotalQuantity(reports) {
  return reports.reduce((sum, r) => sum + (r.totalQuantity || 0), 0)
}

export function calculateUniqueWorkers(reports) {
  const allWorkers = reports.flatMap(r => Object.keys(r.reportData || {}))
  return new Set(allWorkers).size
}
