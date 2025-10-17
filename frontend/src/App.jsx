import { useEffect, useMemo, useState } from 'react'

const API ='/api'

export default function App() {
	const [tasks, setTasks] = useState([])
	const [text, setText] = useState('')
	const [loading, setLoading] = useState(false)
	const [error, setError] = useState(null)

	async function load() {
		setLoading(true)
		setError(null)
		try {
			const res = await fetch(`${API}/tasks`)
			if (!res.ok) throw new Error(`HTTP ${res.status}`)
			const data = await res.json()
			setTasks(data)
		} catch (e) {
			setError(e?.message || 'Failed to load')
		} finally {
			setLoading(false)
			}
	}

	useEffect(() => { load() }, [])

const [adding, setAdding] = useState(false)

async function addTask() {
  const body = { text: text.trim() }
  if (!body.text || adding) return
  setAdding(true)
  const optimistic = { id: Date.now(), text: body.text, done: 0, created_at: new Date().toISOString() }
  setTasks(prev => [optimistic, ...prev])
  setText('')

  try {
    const res = await fetch(`${API}/tasks`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    })
    if (!res.ok) throw new Error(`HTTP ${res.status}`)
    const created = await res.json()
    setTasks(prev => prev.map(t => (t.id === optimistic.id ? created : t)))
  } catch (e) {
    // revert on failure
    setTasks(prev => prev.filter(t => t.id !== optimistic.id))
    console.error(e)
  } finally {
    setAdding(false)
    load() // background truth-sync
  }
}

	async function toggleDone(task) {
		try {
			const res = await fetch(`${API}/tasks/${task.id}`, { 
				method: 'PUT',
				headers: {'Content-Type': 'application/json' },
				body: JSON.stringify({ done: !Boolean(task.done) }),
			})
			if (!res.ok) throw new Error(`HTTP ${res.status}`)
			const updated = await res.json()
			setTasks(prev => prev.map(t => (t.id === updated.id ? updated : t)))
		} catch (e) { console.error(e) }
	}
	async function remove(task) {
    try {
      const res = await fetch(`${API}/tasks/${task.id}`, { method: 'DELETE' })
      if (!res.ok) throw new Error(`HTTP ${res.status}`)
      setTasks(prev => prev.filter(t => t.id !== task.id))
    } catch (e) { console.error(e) }
  }
	const count = useMemo(() => tasks.length, [tasks])

	return (
		<div className="container">
		<h1>Vite + React + PHP (JS)</h1>
		<div className="card">
		<div className="row">
		<input
		type="text"
		placeholder="Add a task..."
		value={text}
		onChange={e => setText(e.target.value)}
		onKeyDown={e => { if (e.key === 'Enter') addTask() }}
		/>
		<button onClick={addTask}>Add</button>
		<button onClick={load} title="Reload">↻</button>
		</div>
		<p className="muted">{loading ? 'Loading…' : `${count} task${count === 1 ? '' : 's'}`}{error ? ` • ${error}` : ''}</p>

        <ul>
          {tasks.map(t => (
            <li key={t.id}>
              <input
                type="checkbox"
                checked={Boolean(t.done)}
                onChange={() => toggleDone(t)}
                aria-label="toggle done"
              />
              <span className={Boolean(t.done) ? 'done' : undefined}>{t.text}</span>
              <span className="muted">· {new Date(t.created_at).toLocaleString()}</span>
              <div className="right">
                <button onClick={() => remove(t)} aria-label="delete">Delete</button>
              </div>
            </li>
          ))}
        </ul>
      </div>
    </div>
  )
}
