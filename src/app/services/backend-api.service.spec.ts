import { TestBed } from '@angular/core/testing';
import { HttpClientTestingModule, HttpTestingController } from '@angular/common/http/testing';
import { BackendApiService } from './backend-api.service';

describe('BackendApiService', () => {
  let service: BackendApiService;
  let httpMock: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [HttpClientTestingModule],
      providers: [BackendApiService]
    });
    service = TestBed.inject(BackendApiService);
    httpMock = TestBed.inject(HttpTestingController);
    localStorage.clear();
  });

  afterEach(() => {
    httpMock.verify();
    localStorage.clear();
  });

  it('should be created', () => {
    expect(service).toBeTruthy();
  });

  it('should login successfully', () => {
    const mockResponse = {
      success: true,
      token: 'test-token',
      user: { id: 1, email: 'test@example.com' }
    };

    service.login({ email: 'test@example.com', password: 'password' }).subscribe(response => {
      expect(response.success).toBe(true);
      expect(response.token).toBe('test-token');
      expect(localStorage.getItem('akani_auth_token')).toBe('test-token');
    });

    const req = httpMock.expectOne('http://www.s3smart.com.br/backend/api/auth/login');
    expect(req.request.method).toBe('POST');
    req.flush(mockResponse);
  });

  it('should get users with authentication', () => {
    localStorage.setItem('akani_auth_token', 'test-token');
    
    const mockResponse = {
      success: true,
      users: [{ id: 1, email: 'test@example.com' }]
    };

    service.getUsers().subscribe(response => {
      expect(response.success).toBe(true);
      expect(response.users).toBeDefined();
    });

    const req = httpMock.expectOne('http://www.s3smart.com.br/backend/api/users');
    expect(req.request.method).toBe('GET');
    expect(req.request.headers.get('Authorization')).toBe('Bearer test-token');
    req.flush(mockResponse);
  });

  it('should check if authenticated', () => {
    expect(service.isAuthenticated()).toBe(false);
    localStorage.setItem('akani_auth_token', 'test-token');
    expect(service.isAuthenticated()).toBe(true);
  });
});

