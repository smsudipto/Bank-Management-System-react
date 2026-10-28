import { CircleHelp } from 'lucide-react'
import { Link } from 'react-router-dom'

function NotFound() {
  return <div className="page-content"><div className="not-found"><CircleHelp className="not-found-icon" size={46} /><h2>Page not found</h2><p>The page you are looking for does not exist or may have moved.</p><Link className="submit-button" to="/">Back to dashboard</Link></div></div>
}

export default NotFound
