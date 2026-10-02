/**
 * User Domain Model
 * Represents the authenticated user entity with business logic,
 * getters, validation, and serialization.
 */
export interface UserAttributes {
  id: number
  name: string
  email: string
  is_verified?: boolean
  created_at?: string | Date | null
  updated_at?: string | Date | null
}

export class User {
  public id: number
  public name: string
  public email: string
  public isVerified: boolean
  public createdAt: Date | null
  public updatedAt: Date | null

  constructor(attributes: UserAttributes) {
    this.id = Number(attributes.id) || 0
    this.name = attributes.name || ''
    this.email = attributes.email || ''
    this.isVerified = Boolean(attributes.is_verified)
    this.createdAt = attributes.created_at ? new Date(attributes.created_at) : null
    this.updatedAt = attributes.updated_at ? new Date(attributes.updated_at) : null
  }

  /**
   * Returns user initials (e.g. "John Doe" -> "JD")
   */
  public get initials(): string {
    if (!this.name) return 'U'
    const parts = this.name.trim().split(/\s+/)
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase()
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
  }

  /**
   * Returns formatted display name
   */
  public get displayName(): string {
    return this.name || this.email.split('@')[0] || 'User'
  }

  /**
   * Human-readable member since string (e.g. "October 1, 2026")
   */
  public get formattedCreatedAt(): string {
    if (!this.createdAt || isNaN(this.createdAt.getTime())) {
      return 'Recent member'
    }
    return new Intl.DateTimeFormat('en-US', {
      month: 'long',
      day: 'numeric',
      year: 'numeric'
    }).format(this.createdAt)
  }

  /**
   * Validates if current email format is acceptable
   */
  public hasValidEmail(): boolean {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)
  }

  /**
   * Creates an exact clone of the current User instance
   */
  public clone(): User {
    return new User({
      id: this.id,
      name: this.name,
      email: this.email,
      is_verified: this.isVerified,
      created_at: this.createdAt,
      updated_at: this.updatedAt
    })
  }

  /**
   * Serializes model to standard JSON object
   */
  public toJSON(): UserAttributes {
    return {
      id: this.id,
      name: this.name,
      email: this.email,
      is_verified: this.isVerified,
      created_at: this.createdAt?.toISOString() ?? null,
      updated_at: this.updatedAt?.toISOString() ?? null
    }
  }

  /**
   * Factory method to instantiate User from backend API payload
   */
  public static fromAPI(data: any): User {
    if (!data) return User.createEmpty()
    return new User({
      id: data.id ?? 0,
      name: data.name ?? '',
      email: data.email ?? '',
      is_verified: data.is_verified ?? data.isVerified ?? false,
      created_at: data.created_at ?? data.createdAt ?? null,
      updated_at: data.updated_at ?? data.updatedAt ?? null
    })
  }

  /**
   * Factory method to create an unauthenticated / guest placeholder
   */
  public static createEmpty(): User {
    return new User({
      id: 0,
      name: '',
      email: '',
      is_verified: false,
      created_at: null,
      updated_at: null
    })
  }
}
